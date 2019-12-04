<?php

namespace App\Http\Controllers;

use App\Bid;
use App\Compare;
use App\Player;
use App\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BridgeController extends BaseController
{

    function login(Request $request)
    {
        try {

            $request->validate([
                'name' => ['required'],
            ]);
            if (count(Player::all()) > 1) {
                return response()->json('the room is full.', 400);
            }
            Player::create([
                'name' => $request->name,
                'trick' => 0
            ]);

            return response()->json(Player::all(), 200);

        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), 400);
        }
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
            $request->validate([
                'name' => ['exists:cards'],
                'trump' => ['required', 'min:100','max:500'],
                'line' => ['required', 'integer','max:7']
            ]);
            if (Bid::latest()->first()) {
                $trump = Bid::latest()->first()->trump; //100,200,300,400
                $line = Bid::latest()->first()->line; //1,2,3,4,5...
                $bid = $trump + $line * 1000;
                $total = $request['line'] * 1000 + $request['trump'];
                if ($bid > $total) {
                    return $this->sendError("Illigal bid.", 422);
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
            return Bid::latest()->first();
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), 400);
        }
    }

    function turnOver()
    {
        return Card::where('name', 'Pile')->first();
    }

    function play(Request $request)
    {
//        dd(Player::find(2)->update([
//            'trick' => 1
//        ]));
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
                        return $this->sendError("Not your turn", 400);
                    }
                }else{
                    if ($priority == 0) {
                        return $this->sendError("Not your turn", 400);
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

                if($priority == 0){
                    $first = Compare::latest()->first()->color;
                    $sameColor = count(Card::where('name', $request['name'])->where('color', $first)->get());
                    if($sameColor > 0 && $request['color']!= $first){
                        return $this->sendError("Illegal play.", 400);
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
                $data = Compare::orderBy('id', 'desc')->get();
                DB::commit();
                return $data;

            } else {
                return $this->sendError("You don't have this card.", 400);
            }

        } catch (Exception $error) {
            DB::rollback();
            return $this->sendError($error->getMessage(), 400);
        }

    }

    function judge(Request $request)
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
                return $this->sendResponse($result, 200);
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
                $winner = $this->compare($playerA, $playerB, $round)->name;
                $num = Compare::where('round', $round)->where('priority', null)->get()->count();
                if ($num > 0) {

                    Compare::where('round', $round)->where('name', $winner)->first()->update([
                        'priority' => 1
                    ]);
                    Compare::where('round', $round)->where('priority', null)->first()->update([
                        'priority' => 0
                    ]);
                }
                if(count(Compare::all()) > 27){
                    $trick = Player::where('name', $winner)->first()->trick;
                    $goal = Player::where('name', $winner)->first()->goal;
                    if ($trick == $goal) {
                        $data['winner'] = $winner;
                        $data['message'] = "Game over.";
                        return $this->sendResponse($data, 200);
                    }
                }


            }
            $data['winner'] = $winner;
            $data['comparison'] = Compare::orderBy('id', 'DESC')->get();
            return $this->sendResponse($data, 200);
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), 400);
        }

    }

    function compare($playerA, $playerB, $round)
    {
        $trump = Bid::latest()->first()->trump;
        $colorA = $playerA->color;
        $colorB = $playerB->color;

        $haveTrump = 0;
        if ($trump) {
            if ($playerB->color == $trump) {

                $colorB = 500;
                $haveTrump = 1;
            }
            if ($playerA->color == $trump) {
                $colorA = 500;
                $haveTrump = 1;
            }
            if (!$haveTrump) {
                if ($playerB->color == $colorA) {
                    $colorB = 500;
                }
                $colorA = 500;
            }
        }

        $data[0] = $pointA = $colorA + $playerA->card;
        $data[1] = $pointB = $colorB + $playerB->card;
        if ($pointA > $pointB) {
            $winner = $playerA;
            $loser = $playerB;
        } else if ($pointA < $pointB) {
            $winner = $playerB;
            $loser = $playerA;
        }
        if (count(Compare::all()) < 27) {
            $num = Compare::where('round', $round)->where('priority', null)->get()->count();
            if ($num != 0) {
                $card = $this->turnOver();
                $card->update([
                    'name' => $winner->name,
                ]);

            }
            $card = $this->turnOver();
            $card->update([
                'name' => $loser->name,
            ]);
            $card = $this->turnOver();
            $card->update([
                'name' => $loser->name,
            ]);

        } else { //next phase
            if(Player::find(1)->trick + Player::find(2)->trick < ($round-13)){
                $trick = Player::where('name', $winner->name)->first()->trick + 1;
                Player::where('name', $winner->name)->first()->update([
                    'trick' => $trick,
                ]);
            }

        }
        return $winner;
    }


    function card()
    {
        $data['piles num'] = count(Card::where('name', 'pile')->get());
        $data['card'] = Card::all();
        return response()->json($data);
    }

    function over()
    {
        DB::table('players')->truncate();
        return response()->json('leaved');
    }
}
