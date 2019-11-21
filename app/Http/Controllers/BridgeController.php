<?php

namespace App\Http\Controllers;

use App\Bid;
use App\Compare;
use App\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BridgeController extends BaseController
{

    function distribute()
    {
        DB::table('players')->truncate();
        DB::table('bids')->truncate();

        $suit = array("400", "300", "200", "100");

        for ($i = 0; $i < count($suit); $i++) {
            for ($j = 2; $j <= 14; $j++) {
                $cards[] = $suit[$i] . " " . $j;
            }
        }

        shuffle($cards);
        for ($i = 0; $i < 13; $i++) {
            $str_sec = explode(" ", $cards[$i]);
            Player::create([
                'name' => 'Lulu',
                'color' => $str_sec[0],
                'card' => $str_sec[1],
            ]);
        }
        for ($i = 13; $i < 26; $i++) {
            $str_sec = explode(" ", $cards[$i]);
            Player::create([
                'name' => '阿寶',
                'color' => $str_sec[0],
                'card' => $str_sec[1],
            ]);
        }
        for ($i = 26; $i < 52; $i++) {
            $str_sec = explode(" ", $cards[$i]);
            Player::create([
                'name' => 'Pile',
                'color' => $str_sec[0],
                'card' => $str_sec[1],
            ]);
        }
        return Player::all();
    }

    function bid(Request $request)
    {
        try {
            if (Bid::latest()->first()) {
                $trump = Bid::latest()->first()->trump;
                $line = Bid::latest()->first()->line;
                if ($line > $request['line'] || $trump > $request['trump']) {
                    return $this->sendError("Line must be greater than $line", 422);
                }
            }
            $request->validate([
                'name' => ['exists:players'],
                'trump' => ['required', 'max:4'],
                'line' => ['required', 'integer', 'max:7']
            ]);
            Bid::create([
                'player' => $request['name'],
                'trump' => $request['trump'],
                'line' => $request['line'],
            ]);

            return Bid::latest()->first();

        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), 400);
        }

    }

    function turnOver()
    {
        return Player::where('name', 'Pile')->first();
    }

    function play(Request $request)
    {

        try {
            $request->validate([
                'name' => ['exists:players'],
                'color' => ['required'],
                'card' => ['required'],
            ]);

            $exist = Player::where('name', $request['name'])
                ->where('color', $request['color'])
                ->where('card', $request['card'])->count();
            if ($exist) {

                Compare::create([
                    'name' => $request['name'],
                    'color' => $request['name'],
                    'card' => $request['card'],
                ]);

                return Compare::all();

            }
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), 400);
        }

    }

    function judge(Request $request)
    {
        try {
            $round = $request['round'];
            $players = Compare::where('round', $round + 1)->get();
            if (count($players) < 2) {
                $result['winner'] = 'Not yet';
                return $this->sendResponse($result, 200);
            }else{
                $winner = $this->compare($players[0], $players[1]);
            }



            return $this->sendResponse($winner, 200);
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), 400);
        }

    }

    function compare($playerA, $playerB)
    {
        $trump = Bid::latest()->first()->trump;
        $colorA = $playerA->color;
        $colorB = $playerB->color;
        if ($trump) {
            if ($playerA->color == $trump) {
                $colorA = 500;
            }
            if ($playerB->color == $trump) {
                $colorB = 500;
            }
        }
        $pointA = $colorA + $playerA->card;
        $pointB = $colorB + $playerB->card;

        if ($pointA > $pointB) {
            $winner = $playerA;
        } else if ($pointA < $pointB) {
            $winner = $pointB;
        }
        return $winner;
    }


}
