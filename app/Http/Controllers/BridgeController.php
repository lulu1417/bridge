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

    function distribute()
    {
        DB::table('players')->truncate();
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
                'name' => 'Lulu',
                'color' => $str_sec[0],
                'card' => $str_sec[1],
            ]);

        }
        Player::create([
            'name' => 'Lulu',
            'trick' => 0,
        ]);
        for ($i = 13; $i < 26; $i++) {
            $str_sec = explode(" ", $cards[$i]);
            Card::create([
                'name' => '阿寶',
                'color' => $str_sec[0],
                'card' => $str_sec[1],
                'trick' => 0
            ]);

        }
        Player::create([
            'name' => '阿寶',
            'trick' => 0,
        ]);
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
                'trump' => ['required', 'max:4'],
                'line' => ['required', 'integer', 'max:70']
            ]);
            if (Bid::latest()->first()) {
                $trump = Bid::latest()->first()->trump; //1,2,3,4
                $line = Bid::latest()->first()->line; //10,20,30,40,50...
                $bid = $trump + $line;
                $total = $request['line'] + $request['trump'];
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
                    $goalB = 8 - Bid::latest()->first()->line / 10;
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

        try {
            DB::beginTransaction();
            $request->validate([
                'name' => ['exists:cards'],
                'color' => ['required'],
                'card' => ['required'],
                'round' => ['required'],
            ]);

            $exist = Card::where('name', $request['name'])
                ->where('color', $request['color'])
                ->where('card', $request['card'])->count();
            if ($exist) {
                $card = Card::where('name', $request['name'])
                    ->where('color', $request['color'])
                    ->where('card', $request['card']);

                Compare::create([
                    'name' => $request['name'],
                    'color' => $request['color'],
                    'card' => $request['card'],
                    'round' => $request['round'],
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
            $round = $request['round'];
            $players = Compare::where('round', $round)->get();
            if (count($players) < 2) {
                $result['winner'] = 'Not yet';
                return $this->sendResponse($result, 200);
            } else {
                if ($round > 1) {
                    $playerA = Compare::where('round', $round - 1)->where('priority', '1')->first();
                    $playerB = Compare::where('round', $round - 1)->where('priority', '0')->first();
                } else {
                    $playerA = Compare::where('round', $round)->get()[0];
                    $playerB = Compare::where('round', $round)->get()[1];
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


            }
            $data['winner'] = $winner;
            $data['comparison'] = Compare::all();
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
            }if ($playerA->color == $trump) {
                $colorA = 500;
                $haveTrump = 1;
            }
            if($haveTrump){
                $colorA = 500;
            }
        }
        $pointA = $colorA + $playerA->card;
        $pointB = $colorB + $playerB->card;

        if ($pointA > $pointB) {
            $winner = $playerA;
            $loser = $playerB;
        } else if ($pointA < $pointB) {
            $winner = $playerB;
            $loser = $playerA;
        }
        Player::where('name', $winner->name)->first()->trick;
        $num = Compare::where('round', $round)->where('priority', null)->get()->count();
        if ($num != 0) {
            $card = $this->turnOver();
            $card->update([
                'name' => $winner->name,
            ]);

            $trick = Player::where('name', $winner->name)->first()->trick + 1;

            $goal = $winner->goal;
            if ($trick == $goal) {
                $data['winner'] = $winner->name;
                $date['message'] = "Game over.";
                return $this->sendResponse($data, 200);;
            }
            Player::where('name', $winner->name)->first()->update([
                'trick' => $trick,
            ]);

            $card = $this->turnOver();
            $card->update([
                'name' => $loser->name,
            ]);

        }
        return $winner;
    }

    function card()
    {
        return Card::all();
    }

}
