<?php

namespace App\Http\Controllers;

use App\Bid;
use App\Compare;
use App\Player;
use App\Card;
use App\Http\Controllers\Judge as Judge;
use App\Room;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BridgeController extends BaseController
{

    function login(Request $request)
    {
        try {
            Log::info($request->all());
            $validator = validator::make($request->all(), [
                'name' => ['required', 'unique:players'],
                'password' => ['required'],
            ]);

            if ($validator->fails()) {
                return $this->sendError("Name or password not given, or the name has been taken.", 1, 400);
            }
            if (count(Player::all()) > 1) {
                $result['message'] = "The room is full.";
                $result['players'] = Player::all();
                return response()->json($result, 400);
            }
            Player::create([
                'name' => $request->name,
                'password' => $request->password,
                'trick' => 0,
            ]);
            if (count(Player::all()) == 2) {
                $judge = new Judge;
                $judge->distribute();
            }
            $result = Player::all();
            return response()->json($result, 200);
        } catch
        (Exception $error) {
            return $this->sendError($error->getMessage(), 400);
        }
    }

    function bid(Request $request)
    {
        try {
            Log::info($request->all());
            $rules = [
                'name' => ['exists:cards'],
                'trump' => ['required', 'integer', 'min:100', 'max:500'],
                'line' => ['required', 'integer', 'max:7']
            ];
            $validator = validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->sendError(
                    "Player's name not found. Trump may not be greater than 500, or line may not be greater than 7",
                    3, 400);
            }

            if (Bid::latest()->first()) {
                $trump = Bid::latest()->first()->trump; //100,200,300,400
                $line = Bid::latest()->first()->line; //1,2,3,4,5...
                $last = Bid::latest()->first()->player;
                $bid = $trump + $line * 1000;
                $total = $request['line'] * 1000 + $request['trump'];
                if ( Bid::latest()->first()->isPass == 1) {
                    $response['message'] = "is already deal";
                    $response['last'] = Bid::latest()->first();
                    return $this->sendError($response, 5, 400);
                }elseif($request['name'] == $last){
                    $response['message'] = "not your turn";
                    $response['last'] = Bid::latest()->first();
                    return $this->sendError($response, 5, 400);
                }
                if ($bid > $total || ($request['trump'] == $trump && $request['line'] != $line)) {
                    $response['message'] = "Illigal bid";
                    $response['last'] = Bid::latest()->first();
                    return $this->sendError($response, 4, 400);
                } else if ($bid == $total) {
                    Bid::create([
                        'player' => $request['name'],
                        'trump' => $trump,
                        'line' => $line,
                        'isPass' => 1,
                    ]);
                    $playerB = Player::where('name', Bid::where('isPass', '1')->first()->player)->first();
                    $goalB = 8 - Bid::latest()->first()->line;
                    $playerB->update([
                        'goal' => $goalB,
                    ]);
                    $playerA = Player::where('name', Bid::orderBy('id', 'desc')->take(2)->get()[1]->player)->first();
                    $goalA = 14 - $goalB;
                    $playerA->update([
                        'goal' => $goalA,
                    ]);
                    return Bid::all();
                }
            } else {
                if ($request['name'] != Player::first()->name) {
                    $response['message'] = "Not your turn";
                    return $this->sendError($response, 5, 400);
                }

            }
            Bid::create([
                'player' => $request['name'],
                'trump' => $request['trump'],
                'line' => $request['line'],
                'isPass' => 0,
            ]);
            return Bid::all();
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), 99, 400);
        }
    }

    function play(Request $request)
    {
        try {
            Log::info($request->all());
            DB::beginTransaction();
            $request->validate([
                'name' => ['exists:cards'],
                'color' => ['required'],
                'card' => ['required'],
            ]);
            if (count(Compare::all()) < 2) {
                $round = 1;
                if (count(Compare::all()) == 0) {
                    if (Bid::latest()->first()->player == $request->name) {
                        return $this->sendError("Not your turn", 5, 400);
                    }
                } else {
                    $compare_id = Compare::latest()->first()->id;
                    if (Compare::latest()->first()->name == $request->name) {
                        return $this->sendError("Not your turn", 5, 400);
                    }
                }
            } else { //validate priority
                $compare_id = Compare::latest()->first()->id;
                if (count(Compare::all()) % 2 == 0) {
                    $round = Compare::latest()->first()->round + 1;
                } else {
                    $round = Compare::latest()->first()->round;
                }
                $priority = Compare::where('name', $request->name)->latest()->first()->priority;
                if ( $compare_id == 26) {
                    if (Bid::latest()->first()->player != $request->name) {
                        return $this->sendError("Not your turn", 5, 400);
                    }
                } else {
                    if (count(Compare::all()) % 2 == 1) {
                        if (( $priority == 1 && $compare_id != 27) || $priority === null ) {
                            return $this->sendError("Not your turn", 5, 400);
                        }
                    } elseif ($priority == 0) {
                        return $this->sendError("Not your turn", 5, 400);
                    }
                }

            }
            $exist = Card::where('name', $request['name'])
                ->where('color', $request['color'])
                ->where('card', $request['card'])->count();
            if ($exist) {
                $card = Card::where('name', $request['name'])
                    ->where('color', $request['color'])
                    ->where('card', $request['card']);

                if (count(Compare::all()) > 0 && ( ($round == 1 || ($compare_id % 2 == 1)  && $priority == 0))){
                    $first = Compare::latest()->first()->color;
                    $sameColor = count(Card::where('name', $request['name'])->where('color', $first)->get());
                    if ($sameColor > 0 && $request['color'] != $first) {
                        return $this->sendError("Illegal play.", 6, 400);
                    }
                }
                Compare::create([
                    'name' => $request['name'],
                    'color' => $request['color'],
                    'card' => $request['card'],
                    'round' => $round,
                ]);
                $card->update([
                    'name' => 'discard',
                ]);
                DB::commit();
                if (count(Compare::where('round', $round)->get()) == 2) {
                    Log::debug("compare");
                    $compare = new Judge;
                    $compare->judge();
                } else {
                    Log::debug("not yet compare");
                }
                $data = Compare::orderBy('id', 'desc')->get();
                return $data;

            } else {
                return $this->sendError("You don't have this card.", 7, 400);
            }

        } catch (Exception $error) {
            DB::rollback();
            return $this->sendError($error->getMessage(), 99, 400);
        }
    }

    function status(Request $request)
    {
        $data['status'] = 'the room is empty';
        $data['bid'] = '';
        $data['pile'] = '';
        $data["pile's_num"] = count(Card::where('name', 'pile')->get());
        $data['new_card'] = '';
        $data['compare'] = '';
        $data['round'] = 0;
        $data['room'] = Player::all();
        $data['card'] = Card::where('name', $request->name)->orderBy('color', 'ASC')->orderBy('card', 'ASC')->get();
        $num = count(Player::all());
        if ($num == 1) {
            $data['status'] = 'wait for another player';
        } elseif ($num == 2) { //遊戲開始
            $data['status'] = 'your turn';
            if (count(Compare::all()) > 0) {
                if ($data['pile\'s_num'] > 0 && $data['pile\'s_num'] < 26) { //換牌階段
                    $data['new_card'] = Card::where('name', $request->name)->orderBy('id', 'DESC')->first();
                }
                $round = Compare::latest()->first()->round;
                $data['compare'] = Compare::where('round', $round)->get();
                if ($round == 13) {
                    if (Bid::latest()->first()->player != $request->name) {
                        $data['status'] = "not you";
                    }
                } else {
                    if (Compare::latest()->first()->priority != null) {
                        if (Compare::where('priority', 1)->latest()->first()->name != $request->name) {
                            $data['status'] = "not you";
                        }
                        $round += 1;
                    } else {
                        if (Compare::latest()->first()->name == $request->name) {
                            $data['status'] = "not you";
                        }
                    }
                }
                $data['round'] = $round;
                $data['bid'] = Bid::latest()->first()->only('player', 'trump', 'line', 'isPass');
                $data['pile'] = Card::where('name', 'pile')->first();
                if (Player::first()->trick === Player::first()->goal || Player::latest()->first()->trick === Player::latest()->first()->goal) {
                    $data['status'] = "game over";
                }
            } else {
                if (count(Bid::all()) > 0) { //喊牌階段
                    if (Bid::latest()->first()->player == $request->name) {
                        $data['status'] = "not you";
                    }
                    $data['bid'] = Bid::latest()->first()->only('player', 'trump', 'line', 'isPass');
                } else { //正要開始喊牌
                    if (Player::first()->name != $request->name) {
                        $data['status'] = "not you";
                    }
                }
                $data['pile'] = Card::where('name', 'pile')->first();
            }
        }
        Log::info($data);
        return response()->json($data);
    }

    function leave(Request $request)
    {
        $player = Player::where('name', $request->name)->first();
        $player->delete();
        return $this->sendResponse("$player->name leaved.", 200);
    }
    function clear(Request $request)
    {
        DB::table('players')->truncate();
        return $this->sendResponse("room is cleared.", 200);
    }

    function back(Request $request)
    {
        Log::info($request->all());
        $player = Player::where('name', $request->name)->where('password', $request->password)->first();
        if ($player) {
            return response()->json(Player::all(), 200);
        } else {
            return $this->sendError("Wrong name or password！", 8, 400);
        }
    }

    function reset()
    {
        DB::table('bids')->truncate();
        DB::table('compares')->truncate();
        $judge = new Judge;
        $judge->distribute();
        return response()->json('reset.', 200);
    }
}
