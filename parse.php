#!/usr/bin/php -q
<?

$std_in = fopen('php://stdin', 'rb');
$std_out = fopen('php://stdout', 'w');

$log = fopen('/var/www/html/parse.log', 'w');

$arr = array();

while( !feof($std_in) && $temp = fgets($std_in) )
    {
    fputs($log, $temp);

    $temp = str_replace("\n", "", $temp);

    if(strlen($temp) == 0)
        break;

    $str = explode(": ", $temp, 2);

    if(!isset($str[1]))
        $str[1] = "";

    $arr[ str_replace("agi_", "", $str[0]) ] = $str[1];
    }

fputs($log, print_r($arr, true));

if(isset($arr['arg_1'])) {

    if(preg_match("/confidence=([\d]+)/", $arr['arg_1'], $res) && sizeof($res) == 2) {

        fputs($log, print_r($res, true));

        if( (int) $res[1] >= 40 ) {

            if(preg_match("/<SWI_meaning>([\d]+)<\/SWI_meaning>/", $arr['arg_1'], $ret)) {

                fputs($log, print_r($ret, true));

                $tl = get_info($ret[1]);

                fputs($log, print_r($tl, true));

                if($tl !== FALSE) {

                    if(strlen($tl['loct']) > 0) {

                        sendrecvcmd("set variable PLAY \"Звоним ".$tl['name']." на номер ".$tl['loct']."\"");

                        sendrecvcmd("set variable TEL \"".$tl['loct']."\"");
                        sendrecvcmd("set priority to_loc");

                    } elseif(strlen($tl['sott']) > 0) {

                        sendrecvcmd("set variable PLAY \"<?xml version='1.0'?><speak version='1.0' xml:lang='ru-ru' xmlns='http://www.w3.org/2001/10/synthesis'><voice name='Мария8000'>Звоним ".$tl['name']." на номер ".$tl['sott']."</voice></speak>\"");
//                      sendrecvcmd("set variable PLAY \"Звоним ".$tl['name']." на номер ".$tl['sott']."\"");

                        sendrecvcmd("set variable TEL \"".$tl['sott']."\"");
                        sendrecvcmd("set priority to_sot");
                    }
                }
            }

        } else {

            sendrecvcmd("set priority ploho");
        }

    } else {

        fputs($log, "preg_match - not found\n");
        sendrecvcmd("set priority notfound");
    }

} else {

    fputs($log, "AGI_arg_1 - not found\n");
    sendrecvcmd("set priority notfound");

}

fflush($std_out);

fclose($std_in);
fclose($std_out);
fclose($log);

function get_info($id) {

    fputs($GLOBALS['log'], "Get_info: ".$id."\n");

    $ret = FALSE;

    $ff = fopen("/var/www/html/rab.csv", "r") or die("Ошибка!");

    if(!$ff) {

        fputs($GLOBALS['log'], "File csv - not found");
    }

    while($dr = fgetcsv($ff, 1000, "\n")) {

        $dt = explode(";", $dr[0]);

        fputs($GLOBALS['log'], print_r($dt, true));

        if( (int) $id == (int) $dt[0] ) {

            $ret['name'] = $dt[2];
            $ret['loct'] = $dt[3];
            $ret['sott'] = $dt[4];

            fputs($GLOBALS['log'], print_r($ret, true));

            break;
            }
        }

    fclose($ff);

    return $ret;
}

function sendrecvcmd($cmd) {

    sendcmd($cmd);

    $ret = recvcmd();

    return $ret;
}

function sendcmd($cmd) {

    $cmd .= "\n";

    fputs($GLOBALS['log'], "Send cmd: ".$cmd);
    fputs($GLOBALS['std_out'], $cmd);

}

function recvcmd() {

    $ret = fgets($GLOBALS['std_in']);
    fputs($GLOBALS['log'], "Answer: ".$ret."\n");

    return $ret;
}

?>