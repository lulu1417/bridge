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
//                'password' => ['required'],
            ]);

            if ($validator->fails()) {
                return $this->sendError("Name or password not given, or the name has been taken.", 1, 400);
            }
            if (count(Player::all()) > 1) {
                return $this->sendError("The room is full.", 2, 400);
            }
            Player::create([
                'name' => $request->name,
                'password' => 123,
                'trick' => 0
            ]);
            $result = Player::all();
            if (count(Player::all()) == 2) {
                $this->distribute();
            }
            return response()->json($result, 200);
        } catch
        (Exception $error) {
            return $this->sendError($error->getMessage(), 400);
        }
    }

    function room()
    {
        $result = Player::all();
        return response()->json($result, 200);
    }

    function distribute()
    {
        DB::table('cards')->truncate();
        DB::table('bids')->truncate();
        DB::table('compares')->truncate();

        $suit = array("400", "300", "200", "100");

        for ($i = 0; $i < count($suit); $i++) {
            for ($j = 2; $j <= 14; $j++) {
                $cards[] = $suit[$i] . " " . $j;
            }
        }

        shuffle($cards);
        for ($i = 0; $i < 13; $i++) {
            $str_sec = explode(" ", $cards[$i]);
            Card::create([
                'name' => Player::find(1)->name,
                'color' => $str_sec[0],
                'card' => $str_sec[1],
            ]);

        }
        for ($i = 13; $i < 26; $i++) {
            $str_sec = explode(" ", $cards[$i]);
            Card::create([
                'name' => Player::find(2)->name,
                'color' => $str_sec[0],
                'card' => $str_sec[1],
            ]);

        }
        for ($i = 26; $i < 52; $i++) {
            $str_sec = explode(" ", $cards[$i]);
            Card::create([
                'name' => 'pile',
                'color' => $str_sec[0],
                'card' => $str_sec[1],
            ]);
        }
        return Card::all();
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
                    return $this->sendError("Not your turn", 5, 400);
                }
                if ($bid > $total) {
                    return $this->sendError("Illigal bid.", 4, 400);
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

    function turnOver()
    {
        return Card::where('name', 'Pile')->first();
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
            } else { //validate priority
                if (count(Compare::all()) % 2 == 0) {
                    $round = Compare::latest()->first()->round + 1;
                } else {
                    $round = Compare::latest()->first()->round;
                }

                $priority = Compare::where('name', $request->name)->latest()->first()->priority;

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
                    $
                    $winner = $this->judge()->winner;
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

    function judge()
    {
        try {
            if (count(Compare::all()) > 2) {
                $round = Compare::latest()->first()->round;
            } else {
                $round = 1;
            }
            $players = Compare::where('round', $round)->get();
            if (count($players) < 2) {
                $result['winner'] = 'Not yet';
                return response()->json($result, 200);
            } else {
                //find the winner in the last round
                if (count(Compare::all()) > 2) {
                    $playerAname = Compare::where('round', $round - 1)->where('priority', '1')->first()->name;
                    $playerA = Compare::where('name', $playerAname)->where('round', $round)->first();
                    $playerBname = Compare::where('round', $round - 1)->where('priority', '0')->first()->name;
                    $playerB = Compare::where('name', $playerBname)->where('round', $round)->first();

                } else {
                    $playerA = Compare::find(1);
                    $playerB = Compare::find(2);
                }
                $compare = new Judge;
                $winner = $compare->compare($playerA, $playerB, $round)->name;
                $num = Compare::where('round', $round)->where('priority', null)->get()->count();
                if ($num > 0) {

                    Compare::where('round', $round)->where('name', $winner)->first()->update([
                        'priority' => 1
                    ]);
                    Compare::where('round', $round)->where('priority', null)->first()->update([
                        'priority' => 0
                    ]);
                }
                if (count(Compare::all()) > 27) {
                    $trick = Player::where('name', $winner)->first()->trick;
                    $goal = Player::where('name', $winner)->first()->goal;
                    if ($trick == $goal) {
                        $data['winner'] = $winner;
                        $data['message'] = "Game over.";
                        return response()->json($data, 200);
                    }
                }
            }
            $data['winner'] = $winner;
            $data['comparison'] = Compare::orderBy('id', 'DESC')->get();
            return $data;
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), 99, 400);
        }
    }

    function card(Request $request)
    {
        $data['pile\'s num'] = count(Card::where('name', 'pile')->get());
        $data['round'] = Compare::latest()->first()->round;
        $data['goal'] = Player::where('name', $request->name)->first()->goal;
        $data['trick'] = Player::where('name', $request->name)->first()->trick;
        if (count(Bid::all()) > 0) {
            $data['trump'] = Bid::latest()->first();
        }
        $first = Player::find(1)->name;
//        $data['player1'] = Card::where('name', $first)->get();
//        $second = Player::find(2)->name;
//        $data['player2'] = Card::where('name', $second)->get();
        $data['card'] = Card::where('name', $request->name)->orderBy('color','ASC')->get();
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

    function modify(Request $request, $id)
    {
        dd(Compare::find($id)->update($request->all()));
        dd(Card::find($id)->update($request->all()));
        dd(Bid::find($id)->update($request->all()));
    }
}
