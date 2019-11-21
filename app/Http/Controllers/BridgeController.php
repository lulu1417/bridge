<?php

namespace App\Http\Controllers;

use App\Bid;
use App\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BridgeController extends BaseController
{

    function distribute()
    {
        DB::table('players')->truncate();
        DB::table('bids')->truncate();

        $suit = array("♠", "♥", "♦", "♣");
        $p = array("T", "J", "Q", "K", "A");

        for ($i = 0; $i < count($suit); $i++) {
            for ($j = 2; $j <= 9; $j++) {
                $cards[] = $suit[$i] . $j;
            }
            for ($k = 0; $k < count($p); $k++) {
                $cards[] = $suit[$i] . $p[$k];
            }
        }
        shuffle($cards);
        for ($i = 0; $i < 13; $i++){
            Player::create([
                'name' => 'Lulu',
                'card' => $cards[$i],
            ]);
        }
        for ($i = 13; $i < 26; $i++){
            Player::create([
                'name' => '阿寶',
                'card' => $cards[$i],
            ]);
        }
        for ($i = 26; $i < 52; $i++){
            Player::create([
                'name' => 'Pile',
                'card' => $cards[$i],
            ]);
        }
        return  Player::all();
    }

    function bid(Request $request){
        try{
            $trump = Bid::latest()->first()->trump;
            $line = Bid::latest()->first()->line;
            $request->validate([
                'name' => ['exists:players'],
                'trump' => ['required', 'max:4'],
                'line' => ['required', 'integer','max:7']
            ]);
            if($line > $request['line'] || $trump > $request['trump']){
                return $this->sendError("Line must be greater than $line", 422);
            }
            Bid::create([
                'player' => $request['name'],
                'trump' => $request['trump'],
                'line' => $request['line'],
            ]);

            return Bid::latest()->first();

        }catch (Exception $error){
            return $this->sendError($error->getMessage(), 400);
        }


    }

}
