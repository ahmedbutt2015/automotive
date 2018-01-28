<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\JobTable;
use App\Models\LinkType;
use App\Models\MatchingTable;
use App\Models\UserInput;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private static function stepFive($conf, $job)
    {
        $client = new Client();
        $url = 'https://automotive.internetofthings.ibmcloud.com/driverinsights/jobcontrol/job';
        $res = null;
        try{
            $res = $client->request('GET', $url, [
                'auth' => [
                    $conf->D_user,
                    $conf->D_password
                ],
                'query' => [
                    'tenant_id'=> $conf->D_tenant_id,
                    'job_id'=> $job->job_id
                ]
            ]);
        }catch (ClientException $exception){
            echo $exception->getMessage();
            die();
        }
        if($res->getStatusCode() == '200'){
            $res = json_decode($res->getBody()->getContents());
            return $res->job_status;
        }
        return null;
    }

    private static function stepfour($conf, $user_input)
    {
        $client = new Client();
        $url = 'https://automotive.internetofthings.ibmcloud.com/driverinsights/jobcontrol/job';
        $res = null;
        try{
            $res = $client->request('POST', $url, [
                'auth' => [
                    $conf->D_user,
                    $conf->D_password
                ],
                'query' => [
                    'tenant_id'=> $conf->D_tenant_id
                ],
                RequestOptions::JSON => [
                    "from"=> $user_input->timestamp,
                    "to"=> $user_input->timestamp,
                ]
            ]);
        }catch (ClientException $exception){
            echo $exception->getMessage();
            die();
        }
        if($res->getStatusCode() == '200'){
            $res = json_decode($res->getBody()->getContents());
            $job = JobTable::create([
                "job_id" => $res->job_id
            ]);
            return $job;
        }
        return null;
    }

    private static function stepthree($conf,$user_input,$match,$linktype)
    {
        $t = [
            "trip_id"=> $user_input->trip_id,
            "timestamp"=> $user_input->timestamp,
            "distance"=> $match->distance,
            "matched_heading"=> $match->matched_heading,
            "speed" => $user_input->speed,
            "matched_longitude" => $match->matched_longitude,
            "mo_id" => $user_input->mo_id,
            "driver_id" => $user_input->driver_id,
            "longitude" => $user_input->longitude,
            "matched_latitude" => $match->matched_latitude,
            "matched_link_id" => $match->link_id,
            "latitude" => $user_input->latitude,
            "road_type" => $linktype["properties"]["type"],
            "heading" => $user_input->heading
        ];
//        dd($t);
        $client = new Client();
        $url = 'https://automotive.internetofthings.ibmcloud.com/driverinsights/datastore/carProbe';
        $res = null;
        try{
            $res = $client->request('POST', $url, [
                'auth' => [
                    $conf->D_user,
                    $conf->D_password
                ],
                'query' => [
                    'tenant_id'=> $conf->D_tenant_id
                ],
                RequestOptions::JSON => [
                    "trip_id"=> $user_input->trip_id,
                    "timestamp"=> $user_input->timestamp,
                    "distance"=> $match->distance,
                    "matched_heading"=> $match->matched_heading,
                    "speed" => $user_input->speed,
                    "matched_longitude" => $match->matched_longitude,
                    "mo_id" => $user_input->mo_id,
                    "driver_id" => $user_input->driver_id,
                    "longitude" => $user_input->longitude,
                    "matched_latitude" => $match->matched_latitude,
                    "matched_link_id" => $match->link_id,
                    "latitude" => $user_input->latitude,
                    "road_type" => $linktype["properties"]["type"],
                    "heading" => $user_input->heading
                ]
            ]);
        }catch (ClientException $exception){
            echo $exception->getMessage();
            die();
        }
        if($res->getStatusCode() == '200'){
            if(json_decode($res->getBody()->getContents())->return_code == 0 ){
                return true;
            }
        }
        return null;
    }

    private static function stepTwo($conf, $match_table)
    {
        $client = new Client();
        $url = 'https://automotive.internetofthings.ibmcloud.com/mapinsights/mapservice/link';
        $res = null;
        try{
            $res = $client->request('GET', $url, [
                'auth' => [
                    $conf->C_user,
                    $conf->C_password
                ],
                'query' => [
                    'tenant_id'=> $conf->C_tenant_id,
                    'link_id'=> $match_table->link_id
                ]
            ]);
        }catch (ClientException $exception){
            echo $exception->getMessage();
            die();
        }
        if($res->getStatusCode() == '200'){
            $res = $res->getBody()->getContents();
            LinkType::create([
                "response" => $res
            ]);
            return json_decode($res,true)['links'][0];
        }
        return false;
    }

    private static function stepOne($conf, $user_input)
    {
        $client = new Client();
        $url = 'https://automotive.internetofthings.ibmcloud.com/mapinsights/mapservice/map/matching';
        $res = null;
        try{
            $res = $client->request('GET', $url, [
                'auth' => [
                    $conf->C_user,
                    $conf->C_password
                ],
                'query' => ['trip_id'=>$user_input->trip_id,
                            'latitude'=> $user_input->latitude,
                            'heading'=> $user_input->heading,
                            'timestamp'=> $user_input->timestamp,
                            'tenant_id'=> $conf->C_tenant_id,
                            'mo_id'=> $user_input->mo_id,
                            'longitude'=> $user_input->longitude ]
            ]);
        }catch (ClientException $exception){
            echo $exception->getMessage();
            die();
        }
        if($res->getStatusCode() == '200'){
            $res = json_decode($res->getBody()->getContents(),true)[0];
            $match = MatchingTable::create($res);
            return $match;
        }
        return null;
    }

    public function save(Request $request){

        $data = $request->all();
        $data['timestamp'] = $request->timestamp .":00Z";
        $user_input = UserInput::create($data);
        if($user_input){
            $request->session()->put('user_id', $user_input->id);
            return redirect('/start');
        }
        $output["error"] = "Something went wrong ! Try again.";
        return back()->with($output);
    }

    public function start(Request $request){
        $user_input = UserInput::find($request->session()->get('user_id'));
        if($user_input){
            $conf = Configuration::first();
            if(!$conf){
                $output["error"] = "Credentials not set.";
                return back()->with($output);
            }
            $match = self::stepOne($conf,$user_input);
            if($match){
                $link = self::stepTwo($conf,$match);
                if($link){
                    self::stepThree($conf,$user_input,$match,$link);
                    $job = self::stepFour($conf,$user_input);
                    if($job){
                        $status = self::stepFive($conf,$job);
                        session()->flash('job',"Job status is " . $status);
                        return redirect('/start');
                    }
                }
            }
        }
        session()->flash('job',"Something went wrong ! Try again.");
        return redirect('/start');
    }
}
