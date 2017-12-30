<?php
$adminPassword = "admin";//管理员密码
$secretsDir="ipsec.secrets";//ipsec.secrets文件的目录
$userLineBegin=3;//ipsec.secrets文件中用户名和密码开始的行数,从0开始
$secretsLogDir="ipsec.log";//日志
$hostname="localhost";//主机名
$requestURL="vpnctr";//目录名
if($_SERVER["REQUEST_METHOD"] == "GET")
{
    header("Location: http://".$hostname."/".$requestURL."/adminCtrPage.html");
    exit;
}
if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $username = $_POST["username"];
    //$oldPassword = $_POST["password"];
    $newUserPass = $_POST["newUserPass"];
    $newUserPassAgain = $_POST["newUserPassAgain"];
    if($_POST["password"] == $adminPassword)
    {
        if($newUserPass == $newUserPassAgain)
        {
            //判断用户是否存在,存在则修改,不存在则添加
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
            if($userExits)
            {
                $secretsFileLines[$userLine]=$username." %any : EAP \"".$newUserPass."\"\n";
                $filePtr=fopen($secretsDir,'w+');
                //fseek($filePtr, $filePosition);
                foreach ($secretsFileLines as $singleLine)
                {
                    fwrite($filePtr, $singleLine);
                }
                //fwrite($filePtr, $fileString);
                fclose($filePtr);
                $logFile=fopen($secretsLogDir, 'a+');
                fwrite($logFile, $_SERVER["REMOTE_ADDR"]."-".date(DATE_W3C)." administrator change \"".$username."\"'s password to \"".$newUserPass."\"\n");
                fclose($logFile);
                system("ipsec restart");
                echo "<script language=javascript>alert(\"密码修改成功!Password has been changed successfully!\");history.back();</script>";
                exit;
            }
            else
            {
                $secretsFileAdd=fopen($secretsDir,'a+');
                fwrite($secretsFileAdd, $_POST['username'].' %any : EAP "'.$_POST['newUserPass'].'"');
                fclose($secretsFileAdd);
                $logFile=fopen($secretsLogDir, 'a+');
                fwrite($logFile, $_SERVER["REMOTE_ADDR"]."-".date(DATE_W3C)." administrator add a new user \"".$_POST['username'].'" : '."\"".$_POST['newUserPass']."\".\n");
                fclose($logFile);
                system("ipsec restart");
                echo "<script language=javascript>alert(\"用户添加成功!New user has been added!\");history.back();</script>";
                exit;
            }
        }
        else
            exit;
    }
    else
        exit;
}
else
    exit;
?>