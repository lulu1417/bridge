<?php

namespace App\Http\Controllers;

use App\Bid;
use App\Compare;
use App\Player;
use App\Card;
use App\Http\Controllers\Judge as Judge;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BridgeController extends BaseController
{

    function login(Request $request)
    {
        try {
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
                'trick' => 0
            ]);
            $result = Player::all();
            if (count(Player::all()) == 2) {
                $judge = new Judge;
                $judge->distribute();
            }
            return response()->json($result, 200);
        } catch
        (Exception $error) {
            return $this->sendError($error->getMessage(), 400);
        }
    }

    function bid(Request $request)
    {
        try {
            $rules = [
                'name' => ['exists:cards'],
                'trump' => ['required', 'integer', 'min:100', 'max:500'],
                'line' => ['required', 'integer', 'max:7']
            ];
            $validator = validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->sendError(
                    "Trump may not be greater than 500, or line may not be greater than 7",
                    3, 400);
            }

            if (Bid::latest()->first()) {
                $trump = Bid::latest()->first()->trump; //100,200,300,400
                $line = Bid::latest()->first()->line; //1,2,3,4,5...
                $last = Bid::latest()->first()->player;
                $bid = $trump + $line * 1000;
                $total = $request['line'] * 1000 + $request['trump'];
                if ($request['name'] == $last) {
                    $response['message'] = "Not your turn";
                    $response['last'] = Bid::latest()->first();
                    return $this->sendError($response, 5, 400);
                }
                if ($bid > $total) {
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
                    return Bid::latest()->first();
                }
            } else {
                if ($request['name'] != Player::find(1)->name) {
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
    function lastBid(){
        return response()->json(Bid::latest()->first());
    }

    function play(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'name' => ['exists:cards'],
                'color' => ['required'],
                'card' => ['required'],
            ]);
            if (count(Compare::all()) < 2) {
                $round = 1;
                if (count(Compare::all()) % 2 == 0 && Bid::latest()->first()->player == $request->name) {
                    return $this->sendError("Not your turn", 5, 400);
                }
            } else { //validate priority
                if (count(Compare::all()) % 2 == 0) {
                    $round = Compare::latest()->first()->round + 1;
                } else {
                    $round = Compare::latest()->first()->round;
                }
                $priority = Compare::where('name', $request->name)->latest()->first()->priority;
                if (Compare::latest()->first()->id == 26) {
                    if (Bid::latest()->first()->player != $request->name) {
                        return $this->sendError("Not your turn", 5, 400);
                    }
                }
                if (count(Compare::all()) % 2 == 1) {
                    if ($priority == 1 || $priority == null) {
                        return $this->sendError("Not your turn", 5, 400);
                    }
                } else {
                    if ($priority == 0) {
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

                if ($round > 1 && $priority == 0) {
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
                    $compare = new Judge;
                    $compare->judge();
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

    function card(Request $request)
    {
        $data['pile\'s_num'] = count(Card::where('name', 'pile')->get());
        $data['room'] = count(Player::all());
        if (count(Bid::all()) > 0) {
            $data['bid'] = Bid::latest()->first()->only('player', 'trump', 'line', 'isPass');
            if (Bid::latest()->first()->isPass == 1 && $data['pile\'s_num'] > 0) {
                $data['pile'] = Card::where('name', 'pile')->first();

            } else {
                $data['pile'] = null;
            }
            if ($data['pile\'s_num'] < 26 ){
                $data['new_card'] = Card::where('name', $request->name)->orderBy('id', 'DESC')->first();
            }else {
                $data['new_card'] = null;
            }
        } else {
            $data['bid'] = null;
        }
        if (count(Compare::all()) > 0) {
            $round = Compare::latest()->first()->round;
            $data['compare'] = Compare::where('round', $round)->get();

        } else {
            $data['compare'] = null;
        }
        $data['card'] = Card::where('name', $request->name)->orderBy('color', 'ASC')->orderBy('card', 'ASC')->get();
        $data['goal'] = Player::where('name', $request->name)->first()->goal;
        $data['trick'] = Player::where('name', $request->name)->first()->trick;
        return response()->json($data);
    }

    function over()
    {
        DB::table('players')->truncate();
        return $this->sendResponse("leaved.", 200);
    }

    function back(Request $request)
    {
        $player = Player::where('name', $request->name)->where('password', $request->password)->first();
        if ($player) {
            return response()->json($player, 200);
        } else {
            return $this->sendError("Wrong name or password！", 8, 400);
        }
    }

}
