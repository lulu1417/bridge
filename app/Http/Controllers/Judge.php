<?php


namespace App\Http\Controllers;


use App\Bid;
use App\Card;
use App\Player;
use App\Compare;

class Judge{
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
            if (Player::find(1)->trick + Player::find(2)->trick < ($round - 13)) {
                $trick = Player::where('name', $winner->name)->first()->trick + 1;
                Player::where('name', $winner->name)->first()->update([
                    'trick' => $trick,
                ]);
            }

        }
        return $winner;
    }

   function turnOver()
    {
        return Card::where('name', 'Pile')->first();
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
//                if (count(Compare::all()) > 27) {
//                    $trick = Player::where('name', $winner)->first()->trick;
//                    $goal = Player::where('name', $winner)->first()->goal;
//                    if ($trick == $goal) {
//                        $data['winner'] = $winner;
//                        $data['message'] = "Game over.";
//                        return response()->json($data, 200);
//                    }
//                }
            }
            $data['winner'] = $winner;
            $data['comparison'] = Compare::orderBy('id', 'DESC')->get();
            return $data;
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), 99, 400);
        }
    }


}
