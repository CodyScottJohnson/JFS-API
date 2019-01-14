<?php
//header("Access-Control-Allow-Origin: *");

$to            = $datas['to']['email']; //$datas->to; // this is your Email address


$from          = $datas['from']['email'];//$datas->from; // this is the sender's Email address
echo $from;
$subject       = $datas['subject'];
$to_fname         = $datas['to']['fname'];
$link    = "https://www.JFSApp.com/ColorQuiz/dist/#/".$datas['Test_Token'];
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
$headers .= "From:".$datas->from->display_name."<" . $from.">";
    // mail($to,$subject,$message,$headers);
 $message2 = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
  <meta name='viewport' content='width=device-width, initial-scale=1' />
  <title>Skyline Confirm Email</title>
  <style type='text/css'>
    @import url(https://fonts.googleapis.com/css?family=Lato:400);

    /* Take care of image borders and formatting */

    img {
      max-width: 600px;
      outline: none;
      text-decoration: none;
      -ms-interpolation-mode: bicubic;
    }

    a {
      text-decoration: none;
      border: 0;
      outline: none;
    }

    a img {
      border: none;
    }

    /* General styling */

    td, h1, h2, h3  {
      font-family: Helvetica, Arial, sans-serif;
      font-weight: 400;
    }

    body {
      -webkit-font-smoothing:antialiased;
      -webkit-text-size-adjust:none;
      width: 100%;
      height: 100%;
      color: #37302d;
      background: #ffffff;
    }

     table {
      border-collapse: collapse !important;
    }


    h1, h2, h3 {
      padding: 0;
      margin: 0;
      color: black;
      font-weight: 400;
    }

    h3 {
      color: #21c5ba;
      font-size: 24px;
    }

    .important-font {
      color: #3B78A3;
      font-weight: bold;
    }

    .hide {
      display: none !important;
    }

    .force-full-width {
      width: 100% !important;
    }
  </style>

  <style type='text/css' media='screen'>
    @media screen {
       /* Thanks Outlook 2013! http://goo.gl/XLxpyl*/
      td, h1, h2, h3 {
        font-family: 'Lato', 'Helvetica Neue', 'Arial', 'sans-serif' !important;
      }
    }
  </style>

  <style type='text/css' media='only screen and (max-width: 480px)'>
    /* Mobile styles */
    @media only screen and (max-width: 480px) {
      table[class='w320'] {
        width: 320px !important;
      }

      table[class='w300'] {
        width: 300px !important;
      }

      table[class='w290'] {
        width: 290px !important;
      }

      td[class='w320'] {
        width: 320px !important;
      }

      td[class='mobile-center'] {
        text-align: center !important;
      }

      td[class*='mobile-padding'] {
        padding-left: 20px !important;
        padding-right: 20px !important;
        padding-bottom: 20px !important;
      }

      td[class*='mobile-block'] {
        display: block !important;
        width: 100% !important;
        text-align: left !important;
        padding-bottom: 20px !important;
      }

      td[class*='mobile-border'] {
        border: 0 !important;
      }

      td[class*='reveal'] {
        display: block !important;
      }
    }
  </style>
</head>
<body class='body' style='padding:0; margin:0; display:block; background:#ffffff; -webkit-text-size-adjust:none' bgcolor='#ffffff'>
<table align='center' cellpadding='0' cellspacing='0' width='100%' height='100%'>
  <tr>
    <td align='center' valign='top' bgcolor='#ffffff'  width='100%'>

    <table cellspacing='0' cellpadding='0' width='100%'>
      <tr>
        <td style='border-bottom: 3px solid #226493;' width='100%'>
          <center>
            <table cellspacing='0' cellpadding='0' width='500' class='w320'>
              <tr>
                <td valign='top' style='padding:10px 0; text-align:left;' class='mobile-center'>
                  <img  height='62' src='https://www.jfsapp.com/Images/Logos/JohnsonFinanceMain.png'>
                </td>
              </tr>
            </table>
          </center>
        </td>
      </tr>
      <tr>
        <td  bgcolor='#F1F4F8' valign='top' style='background-color: #F1F4F8; background-position: center;'>
          <!--[if gte mso 9]>
          <v:rect xmlns:v='urn:schemas-microsoft-com:vml' fill='true' stroke='false' style='mso-width-percent:1000;height:303px;'>
            <v:fill type='tile' src='https://www.filepicker.io/api/file/kmlo6MonRpWsVuuM47EG' color='#8b8284' />
            <v:textbox inset='0,0,0,0'>
          <![endif]-->
          <div>
            <center>
              <table cellspacing='0' cellpadding='0' width='530' height='303' class='w320'>
                <tr>
                  <td valign='middle' style='vertical-align:middle; padding-right: 15px; padding-left: 15px; text-align:left;' height='303'>

                    <table cellspacing='0' cellpadding='0' width='100%'>
                      <tr>
                        <td>
                          <h1>".$to_fname.",</h1><br>
                          <h2>Here is the link to the personality test we talked about. Please let me know if you have any problems taking it.</h2>
                          <br>
                        </td>
                      </tr>
                    </table>

                    <table cellspacing='0' cellpadding='0' width='100%'>
                    <tr>
                    <td class='hide reveal'>
                      &nbsp;
                    </td>
                      <td style='width:150px; height:33px; background-color: #3bcdc3;' >
                        <div>
                          <a href='".$link."' style='background-color:#3B78A3;border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:150px;-webkit-text-size-adjust:none;'>Take Test</a>
                          </div>
                      </td>
                      <td>
                        &nbsp;
                      </td>
                    </tr>
                  </table>
                  </td>
                </tr>
              </table>
            </center>
          </div>
          <!--[if gte mso 9]>
            </v:textbox>
          </v:rect>
          <![endif]-->
        </td>
      </tr>
      <tr>
        <td style='background-color:#ffffff;border-top: 3px solid #226493'>
          <center>
            <table cellspacing='0' cellpadding='0' width='100%' class='w320'>
              <tr>
                <td width='33%'></td>
                <td  width='33%'>
 
                  <table cellspacing='0' cellpadding='30' width='100%'>
                    <tr>
                      <td style='text-align:center;'>
                        <a href='#'>
                          <img width='61' height='51' src='https://www.filepicker.io/api/file/vkoOlof0QX6YCDF9cCFV' alt='twitter' />
                        </a>
                        <a href='#'>
                          <img width='61' height='51' src='https://www.filepicker.io/api/file/fZaNDx7cSPaE23OX2LbB' alt='google plus' />
                        </a>
                        <a href='#'>
                          <img width='61' height='51' src='https://www.filepicker.io/api/file/b3iHzECrTvCPEAcpRKPp' alt='facebook' />
                        </a>
                      </td>
                    </tr>
                  </table>
                </td>
                <td  width='33%'>
                <table class='force-full-width' cellspacing='0' cellpadding='0'>
                      <tr>
                        <td style='text-align: left;'>
                          <span class='important-font'>
                            Scott Johnson <br>
                          </span>
                          533 W 2600 S <br>
                          Suite 135 <br>
                          Bountiful, Ut <br>
                          1(801)296-2000
                        </td>
                      </tr>
                    </table>
                </td>
              </tr>
              
            </table>
          </center>
        </td>
      </tr>
    </table>
    </td>
  </tr>
</table>
</body>
</html>
	";   
  //mail($to, $subject, $message2, $headers);
	sendEmail($to,$subject,$message2);


?>