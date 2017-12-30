<?php
$secretsDir="/usr/local/etc/ipsec.secrets";//ipsec.secrets文件的目录
$userLineBegin=3;//ipsec.secrets文件中用户名和密码开始的行数,从0开始
$secretsLogDir="ipsec.log";//日志
$hostname="localhost";//主机名
$requestURL="vpnctr";//目录名
//print_r($_SERVER);

if($_SERVER['REQUEST_METHOD'] == "GET")
{
    header("Location: http://".$hostname."/".$requestURL."/index.html");
    exit;
}
if($_SERVER['REQUEST_METHOD'] == "POST")
//if(true)//debug
{
    $username = $_POST["username"];
    $oldPassword = $_POST["oldPassword"];
    $newPassword = $_POST["newPassword"];
    $newPasswordAgain = $_POST["newPasswordAgain"];
    /*
    $username = "hzy";
    $oldPassword = "hzy";
    $newPassword = "33";
    $newPasswordAgain = "33";*/ //debug
    if($username && $oldPassword && $newPassword && $newPasswordAgain && 
    	strlen($username)<16 && strlen($oldPassword)<16 && 
    	strlen($newPassword)<16 && strlen($newPasswordAgain)<16)
    {
        if($newPassword == $newPasswordAgain)
        {
            $secretsFileLines = file($secretsDir);
            $userLine = 0;
            $userTotal = count($secretsFileLines);
            $userExits=false;//判断用户是否存在
            for ($userLine = $userLineBegin; $userLine < $userTotal; ++$userLine)
            {
                if($username == explode(" ",$secretsFileLines[$userLine])[0])
                {
                    $userExits=true;
                    break;
                }
            }
            $realOldPassword=explode("\"",explode(" ",$secretsFileLines[$userLine])[4])[1];
            if($userExits && $oldPassword == $realOldPassword)//验证密码
            {
                $secretsFileLines[$userLine]=$username." %any : EAP \"".$newPassword."\"\n";
                //合成最终字符串
                /*
                $fileString="";
                for(;$userLine<$userTotal;++$userLine)
                {
                    $fileString=$fileString.$secretsFileLines[$userLine]."\n";
                }*/
                //定位文件指针
                /*
                $lineNumber=0;
                $filePosition=0;
                for(;$lineNumber<$userLine;++$lineNumber)
                {
                    $filePosition+=strlen($secretsFileLines[$lineNumber]);
                }*/
                $filePtr=fopen($secretsDir,'w+');
                //fseek($filePtr, $filePosition);
                foreach ($secretsFileLines as $singleLine)
                {
                    fwrite($filePtr, $singleLine);
                }
                //fwrite($filePtr, $fileString);
                fclose($filePtr);
                $logFile=fopen($secretsLogDir, 'a+');
                fwrite($logFile, $_SERVER["REMOTE_ADDR"]."-".date(DATE_W3C)." \"".$username."\" changed password to \"".$newPassword."\".\n");
                fclose($logFile);
                system("ipsec restart");
                echo "<script language=javascript>alert(\"密码修改成功!Password has been changed successfully!\");history.back();</script>";
                exit;
            }
            else
            {
                if($userExits)
                {
                    //echo "密码错误";
                    $logFile=fopen($secretsLogDir, 'a+');
                    fwrite($logFile, $_SERVER["REMOTE_ADDR"]."-".date(DATE_W3C)." \"".$username."\" changed password to \"".$newPassword."\".Failed:Password error.\n");
                    fclose($logFile);
                }
                else
                {
                    //echo "用户不存在"; //debug
                    $logFile=fopen($secretsLogDir, 'a+');
                    fwrite($logFile, $_SERVER["REMOTE_ADDR"]."-".date(DATE_W3C)." \"".$username."\" changed password to \"".$newPassword."\".Failed:User does not exits.\n");
                    fclose($logFile);
                }
                echo "<script language=javascript>alert(\"此用户不存在或密码不正确!Incorrect username or password!\");history.back();</script>";
                exit;
            }
        }
        else
        {
            $logFile=fopen($secretsLogDir, 'a+');
            fwrite($logFile, $_SERVER["REMOTE_ADDR"]."-".date(DATE_W3C)." \"".$username."\" changed password to \"".$newPassword."\".Failed:The two inputs are inconsistent.\n");
            fclose($logFile);
            echo "<script language=javascript>alert(\"两次密码输入不一致!The two inputs are inconsistent!\");history.back();</script>";
            exit;
        }
    }
    else
    {
        $logFile=fopen($secretsLogDir, 'a+');
        fwrite($logFile, $_SERVER["REMOTE_ADDR"]."-".date(DATE_W3C).".Failed:Empty inputs.\n");
        fclose($logFile);
        echo "<script language=javascript>alert(\"所有填写都不能留空!All forms can not be empty!\");history.back();</script>";
        exit;
    }
}
else
{
    $logFile=fopen($secretsLogDir, 'a+');
    fwrite($logFile, $_SERVER["REMOTE_ADDR"]."-".date(DATE_W3C).".Failed:System error.\n");
    fclose($logFile);
    echo "<script language=javascript>alert(\"系统错误,请重试!System error,please retry!\");history.back();</script>";
    exit;
}
?>