<?php

namespace App\Http\Controllers;

use App\LogTable;
use App\Models\Configuration;
use App\Models\JobTable;
use App\Models\LinkType;
use App\Models\MatchingTable;
use App\Models\UserInput;
use App\TripDetail;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private static function stepSeven( $conf, $trip_id)
    {
        $client = new Client();
        $url = 'https://automotive.internetofthings.ibmcloud.com/driverinsights/drbresult/trip';
        $res = null;
        try{
            $res = $client->request('GET', $url, [
                'auth' => [
                    $conf->D_user,
                    $conf->D_password
                ],
                'query' => [
                    'trip_uuid' => $trip_id,
                    'tenant_id'=> $conf->D_tenant_id
                ]
            ]);
        }catch (ClientException $exception){
            echo $exception->getMessage();
            die();
        }
        if($res->getStatusCode() == '200'){
            $res = json_decode($res->getBody()->getContents());
            TripDetail::create([
               "trip_uuid" => $trip_id,
                "response" => json_encode($res)
            ]);
            return $res;
        }
        return null;

    }

    private static function stepSix($user_input, $conf, $job)
    {
        $client = new Client();
        $url = 'https://automotive.internetofthings.ibmcloud.com/driverinsights/drbresult/tripSummaryList';
        $res = null;
        try{
            $res = $client->request('GET', $url, [
                'auth' => [
                    $conf->D_user,
                    $conf->D_password
                ],
                'query' => [
                    'trip_id'=>$user_input->trip_id,
                    'tenant_id'=> $conf->D_tenant_id,
                    'job_id'=> $job->job_id,
                    'mo_id' => $user_input->mo_id
                ]
            ]);
        }catch (ClientException $exception){
            echo $exception->getMessage();
            die();
        }
        if($res->getStatusCode() == '200'){
            $res = json_decode($res->getBody()->getContents());
            LogTable::create([
                "job_id" => "six",
                "status" => $res
            ]);
            dd($res);
            return $res;
        }
        return null;
    }

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
            return $res->job_status == "RUNNING" ? self::stepFive($conf,$job) : $res;
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
                    "from"=> $user_input->time_stamp,
                    "to"=> $user_input->time_stamp,
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
                    "timestamp"=> $user_input->time_stamp,
                    "distance"=> $match->distance,
                    "matched_heading"=> $match->matched_heading,
                    "speed" => $user_input->speed,
                    "matched_longitude" => $match->matched_longitude,
                    "mo_id" => $user_input->mo_id,
                    "driver_id" => '123456',
                    "longitude" => $user_input->long,
                    "matched_latitude" => $match->matched_latitude,
                    "matched_link_id" => $match->link_id,
                    "latitude" => $user_input->lat,
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
                            'latitude'=> $user_input->lat,
//                            'heading'=> $user_input->heading,
                            'timestamp'=> $user_input->time_stamp,
                            'tenant_id'=> $conf->C_tenant_id,
                            'mo_id'=> $user_input->mo_id,
                            'longitude'=> $user_input->long ]
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
        $file = json_decode(file_get_contents($_FILES["json"]["tmp_name"]),true);
        $file = array_values(array_values($file)[0])[0];
        foreach ($file as $user_input){
            $user_input["mo_id"] = 300;
            $user_input["trip_id"] = 100;
            $user_input["heading"] = '';
            $user_input = (object)$user_input;
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
                }
            }
        }
        $job = self::stepFour($conf,$user_input);
        if($job){
            $res = self::stepFive($conf,$job);
            LogTable::create([
                "job_id" => $res->job_id,
                "status" => $res->job_status
            ]);
            if($res->job_status == "KILLED"){
                $trips = self::stepSix($user_input,$conf,$job);
                session()->flash('job',"Job status is " . $res->job_status);
                return redirect('/');
            }else{
                echo 'asd';
                $trips = self::stepSix($user_input,$conf,$job);
                if($trips){
                    self::stepSeven($conf,'123456');
                }
            }
        }
        session()->flash('job',"Something went wrong ! Try again.");
        return redirect('/');
    }
}
