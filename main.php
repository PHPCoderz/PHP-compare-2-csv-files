<?php

    ini_set('max_execution_time', 3000);
    ini_set('memory_limit', '2500M');
    $time_start = microtime(true);
    //read file
    $handle = fopen('Leads_001.csv', 'r');
    $handle2 = fopen('Leads_0012.csv', 'r');
    $wholedata = array();
    $emails = array();
    $titles = '';
    $titles2 = '';
    $i = 0;
    while ($row = fgetcsv($handle)) {
        // while ($row2 = fgetcsv($handle2)) {
        //     //echo '1 - '.$row[6].' --> 2 - '.$row2[6].'\n';
        // }
        if($i == 0){
            $titles = $row;
            $i++;
            continue;
        }
        $wholedata[] = implode(',',$row);
        $emails[] = $row[6];
    }
    $wholedata2 = array();
    $emails2 = array();
    $j = 0;
    while ($row2 = fgetcsv($handle2)) {
    //     echo '2 - '.$row2[6].'\n';
        if($j == 0){
            $titles2 = $row2;
            $j++;
            continue;
        }
        $wholedata2[] = implode(',',$row2);
        $emails2[] = $row2[6];
    }
    $emailfile = fopen("email_log.txt", "w") or die("Unable to open file!");
    $mails_only_in_a = array_diff($emails,$emails2);
    $mails_only_in_b = array_diff($emails2,$emails);
    $mail_text = "These users not found in Leads_0012 :- ".print_r($mails_only_in_a,true)."\n";
    $mail_text .= "These users not found in Leads_001 :- ".print_r($mails_only_in_b,true)."\n";
    $columns_only_in_a = array_diff($titles,$titles2);
    $columns_only_in_b = array_diff($titles2,$titles);
    $mail_text .= "These columns not found in Leads_0012 :- ".print_r($columns_only_in_a,true)."\n";
    $mail_text .= "These columns not found in Leads_001 :- ".print_r($columns_only_in_b,true)."\n";
    fwrite($emailfile, $mail_text);
    $logs_main = fopen("logs_main.txt", "w") or die("Unable to open file!");
    $duplicates_log = fopen("duplicate_mails.txt", "w") or die("Unable to open file!");
    foreach ($emails as $key => $value) {
        if(in_array($value,$mails_only_in_a) || in_array($value,$mails_only_in_b)){
            continue;
        }
        //below can be used for duplicate check and getting second array index also
        $second_file_array_index = array_keys($emails2,$value); //find this email id in second array
        $first_file_array_index = array_keys($emails,$value); //find this email id in first array
        if(sizeof($second_file_array_index)>1){
            fwrite($duplicates_log, $value." Found multiple times in Leads_0012 \n");
            continue;
        }
        if(sizeof($first_file_array_index)>1){
            fwrite($duplicates_log, $value." Found multiple times in Leads_001 \n");
            continue;
        }
        //now check individual datas
        foreach(explode(',',$wholedata[$key]) as $in_key => $in_value){
            $title_key = array_keys($titles2,$titles[$in_key]);
            if(sizeof($title_key) <= 0 || empty($title_key)){ //check this tile is also exist in second file
                continue;
            }
            $check_array = explode(',',$wholedata2[$second_file_array_index[0]]);
            if($in_value != $check_array[$title_key[0]]){
                fwrite($logs_main, $value." --> **".$in_value."** in Leads_001 with title **".$titles[$in_key]."** not matching with **".$check_array[$title_key[0]]."** in  Leads_0012 \n");
            }
        }
    }
    //unset ends
    $time_end = microtime(true);
    echo " Execution time : " . (($time_end - $time_start)/60);
?>