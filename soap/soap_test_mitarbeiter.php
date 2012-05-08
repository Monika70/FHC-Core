<?php 
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php'); 
require_once('../include/basis_db.class.php');

$db = new basis_db();
$method = (isset($_GET['method'])?$_GET['method']:'getMitarbeiterFromUID');

$getuid = get_uid(); 
if(!check_lektor($getuid))
	die('Sie haben keine Berechtigung für diese Seite'); 
?>
<html>
	<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <script type="text/javascript" src="../include/js/jqSOAPClient.js"></script> 
        <script type="text/javascript" src="../include/js/jquery.js"></script> 
        <script type="text/javascript" src="../include/js/jqXMLUtils.js"></script> 
        <title>SOAP TestClient für Mitarbeiter</title>
	</head>
	<body>
        <a href ="<?php echo $_SERVER['PHP_SELF'].'?method=getMitarbeiterFromUID'?>">getMitarbeiterFromUID</a><br>
        <a href ="<?php echo $_SERVER['PHP_SELF'].'?method=getMitarbeiter'?>">getMitarbeiter</a><br>
        <a href ="<?php echo $_SERVER['PHP_SELF'].'?method=SearchMitarbeiter'?>">SearchMitarbeiter</a><br>
        <a href ="<?php echo APP_ROOT.'soap/mitarbeiter.wsdl.php'?>">Show WSDL </a><br><br>
        
        <?php 
        if($method=='getMitarbeiterFromUID')
        {
            echo'
	            <form action="'.$_SERVER["PHP_SELF"].'?method=getMitarbeiterFromUID" method="post">
	            <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
	                <tr>
	                    <td align="right">Username:</td>
	                    <td><input id="username" name="username" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['username']) ? $_REQUEST['username'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Passwort:</td>
	                    <td><input id="passwort" name="passwort" type="password" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">UID:</td>
	                    <td><input id="uid" name="uid" type="text" size="30" maxlength="10" value="'.$db->convert_html_chars((isset($_REQUEST['uid']) ? $_REQUEST['uid'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right"></td>
	                    <td>
	                        <input type="submit" value="Absenden (PHP)" name="submit">
	                        <input type="button" onclick="sendSoap();" value="Absenden (JS)">
	                    </td>
	                </tr>
	            </table>
	        </form>';
	        echo '
	        <script type="text/javascript">
	        function gettimestamp()
	        {
	            var now = new Date();
	            var ret = now.getHours()*60*60*60;
	            ret = ret + now.getMinutes()*60*60;
	            ret = ret + now.getSeconds()*60;
	            ret = ret + now.getMilliseconds();
	            return ret;
	        }
	        function sendSoap()
	        {
	        	user = document.getElementById("username").value;
	        	passwort = document.getElementById("passwort").value;
	        	uid = document.getElementById("uid").value;
	        	
	            var soapBody = new SOAPObject("getMitarbeiterFromUID");
	            var authentifizierung = new SOAPObject("authentifizierung");
	            authentifizierung.appendChild(new SOAPObject("username")).val(user);
	            authentifizierung.appendChild(new SOAPObject("passwort")).val(passwort);
	
	            soapBody.appendChild(new SOAPObject("uid")).val(uid);
	            soapBody.appendChild(authentifizierung);
	
	            var sr = new SOAPRequest("getMitarbeiterFromUID",soapBody);
	            SOAPClient.Proxy="'.APP_ROOT.'/soap/mitarbeiter.soap.php?"+gettimestamp();
	
	            SOAPClient.SendRequest(sr, clb_save);
	        }
	
	        function clb_save(respObj)
	        {
	            try
	            {
	                data = JSON.stringify(respObj.Body[0]);                
	                document.getElementById("output").innerHTML="<pre>"+data+"<pre>";
	                
	                alert("ok");
	            }
	            catch(e)
	            {
	            alert(e);
	                var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
	                alert("Fehler: "+fehler);
	            }
	        }
	
	        </script>
	        ';
        }
        elseif($method=='getMitarbeiter')
        {
            echo'
	            <form action="'.$_SERVER["PHP_SELF"].'?method=getMitarbeiter" method="post">
	            <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
	                <tr>
	                    <td align="right">Username:</td>
	                    <td><input id="username" name="username" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['username']) ? $_REQUEST['username'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Passwort:</td>
	                    <td><input id="passwort" name="passwort" type="password" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right"></td>
	                    <td>
	                        <input type="submit" value="Absenden (PHP)" name="submit">
	                        <input type="button" onclick="sendSoap();" value="Absenden (JS)">
	                    </td>
	                </tr>
	            </table>
	        </form>';
	        echo '
	        <script type="text/javascript">
	        function gettimestamp()
	        {
	            var now = new Date();
	            var ret = now.getHours()*60*60*60;
	            ret = ret + now.getMinutes()*60*60;
	            ret = ret + now.getSeconds()*60;
	            ret = ret + now.getMilliseconds();
	            return ret;
	        }
	        function sendSoap()
	        {
	        	user = document.getElementById("username").value;
	        	passwort = document.getElementById("passwort").value;
	       		
	            var soapBody = new SOAPObject("getMitarbeiter");
	            var authentifizierung = new SOAPObject("authentifizierung");
	            authentifizierung.appendChild(new SOAPObject("username")).val(user);
	            authentifizierung.appendChild(new SOAPObject("passwort")).val(passwort);
	            soapBody.appendChild(authentifizierung);
	            
	
	            var sr = new SOAPRequest("getMitarbeiter",soapBody);
	            SOAPClient.Proxy="'.APP_ROOT.'/soap/mitarbeiter.soap.php?"+gettimestamp();
	
	            SOAPClient.SendRequest(sr, clb_save);
	        }
	
	        function clb_save(respObj)
	        {
	            try
	            {
	                data = JSON.stringify(respObj.Body[0]);
	                document.getElementById("output").innerHTML="<pre>"+data+"<pre>";
	                alert("ok");
	            }
	            catch(e)
	            {
	            alert(e);
	                var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
	                alert("Fehler: "+fehler);
	            }
	        }
	
	        </script>
	        ';
		}
		elseif($method=='SearchMitarbeiter')
        {
            echo'
	            <form action="'.$_SERVER["PHP_SELF"].'?method=SearchMitarbeiter" method="post">
	            <table border="0" cellpadding="5" cellspacing="0" bgcolor="#E0E0E0">
	                <tr>
	                    <td align="right">Username:</td>
	                    <td><input id="username" name="username" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['username']) ? $_REQUEST['username'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Passwort:</td>
	                    <td><input id="passwort" name="passwort" type="password" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['passwort']) ? $_REQUEST['passwort'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right">Suchfilter:</td>
	                    <td><input id="filter" name="filter" type="text" size="30" maxlength="255" value="'.$db->convert_html_chars((isset($_REQUEST['filter']) ? $_REQUEST['filter'] : "")).'"></td>
	                </tr>
	                <tr>
	                    <td align="right"></td>
	                    <td>
	                        <input type="submit" value="Absenden (PHP)" name="submit">
	                        <input type="button" onclick="sendSoap();" value="Absenden (JS)">
	                    </td>
	                </tr>
	            </table>
	        </form>';
	        echo '
	        <script type="text/javascript">
	        function gettimestamp()
	        {
	            var now = new Date();
	            var ret = now.getHours()*60*60*60;
	            ret = ret + now.getMinutes()*60*60;
	            ret = ret + now.getSeconds()*60;
	            ret = ret + now.getMilliseconds();
	            return ret;
	        }
	        function sendSoap()
	        {
	        	user = document.getElementById("username").value;
	        	passwort = document.getElementById("passwort").value;
				filter = document.getElementById("filter").value;
	       		
	            var soapBody = new SOAPObject("SearchMitarbeiter");
	            var authentifizierung = new SOAPObject("authentifizierung");
	            authentifizierung.appendChild(new SOAPObject("username")).val(user);
	            authentifizierung.appendChild(new SOAPObject("passwort")).val(passwort);
	            soapBody.appendChild(new SOAPObject("filter")).val(filter);
	            soapBody.appendChild(authentifizierung);
	            
	
	            var sr = new SOAPRequest("SearchMitarbeiter",soapBody);
	            SOAPClient.Proxy="'.APP_ROOT.'/soap/mitarbeiter.soap.php?"+gettimestamp();
	
	            SOAPClient.SendRequest(sr, clb_save);
	        }
	
	        function clb_save(respObj)
	        {
	            try
	            {
	                data = JSON.stringify(respObj.Body[0]);
	                document.getElementById("output").innerHTML="<pre>"+data+"<pre>";
	                alert("ok");
	            }
	            catch(e)
	            {
	            alert(e);
	                var fehler = respObj.Body[0].Fault[0].faultstring[0].Text;
	                alert("Fehler: "+fehler);
	            }
	        }
	
	        </script>
	        ';
		}
        
echo '<div id="output">';
class foo {};

if(isset($_REQUEST['submit']) && $_GET['method']=='getMitarbeiterFromUID')
{
	$client = new SoapClient(APP_ROOT."/soap/mitarbeiter.wsdl.php?".microtime(true)); 
	
	try
	{      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
        $response = $client->getMitarbeiterFromUID($_REQUEST['uid'], $authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
    
    
	 
}
if(isset($_REQUEST['submit']) && $_GET['method']=='getMitarbeiter')
{
	$client = new SoapClient(APP_ROOT."/soap/mitarbeiter.wsdl.php?".microtime(true)); 
	
	try
	{      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];
        $response = $client->getMitarbeiter($authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
    
    
	 
}
if(isset($_REQUEST['submit']) && $_GET['method']=='SearchMitarbeiter')
{
	$client = new SoapClient(APP_ROOT."/soap/mitarbeiter.wsdl.php?".microtime(true)); 
	
	try
	{      	
        $authentifizierung = new foo();
        $authentifizierung->username=$_REQUEST['username'];
        $authentifizierung->passwort=$_REQUEST['passwort'];

        $response = $client->SearchMitarbeiter($_REQUEST['filter'],$authentifizierung);
		
		var_dump($response);
	}
	catch(SoapFault $fault) 
	{
    	echo "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR;
	}
    
    
	 
}

echo '</div>';
?>
