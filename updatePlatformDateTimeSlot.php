<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once('db_cnn/cnn.php');
require_once './vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$method = $_SERVER['REQUEST_METHOD'];

function sendConfirmationEmail($email, $clubName, $date, $time, $priceSubTotal, $priceIVA, $priceTotal) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->SMTPDebug = 0;                                     // Enable verbose debug output
        // $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'mail.intelipadel.com';                     // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                                   // Enable SMTP authentication
        $mail->Username = 'no-reply@intelipadel.com';             // SMTP username
        $mail->Password = 'Mailer123';                            // SMTP password
        $mail->SMTPSecure = 'ssl';                                // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 469;                                        // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom('no-reply@intelipadel.com', 'PadelRoom');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = '¡Tu reserva está confirmada! | PadelRoom';
        $mail->Body = '<!DOCTYPE html>

<html
  lang="en"
  xmlns:o="urn:schemas-microsoft-com:office:office"
  xmlns:v="urn:schemas-microsoft-com:vml"
>
  <head>
    <title></title>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <!--[if mso
      ]><xml
        ><o:OfficeDocumentSettings
          ><o:PixelsPerInch>96</o:PixelsPerInch
          ><o:AllowPNG /></o:OfficeDocumentSettings></xml
    ><![endif]-->
    <!--[if !mso]><!-->
    <!--<![endif]-->
    <style>
      * {
        box-sizing: border-box;
      }

      body {
        margin: 0;
        padding: 0;
      }

      a[x-apple-data-detectors] {
        color: inherit !important;
        text-decoration: inherit !important;
      }

      #MessageViewBody a {
        color: inherit;
        text-decoration: none;
      }

      p {
        line-height: inherit;
      }

      .desktop_hide,
      .desktop_hide table {
        mso-hide: all;
        display: none;
        max-height: 0px;
        overflow: hidden;
      }

      .image_block img + div {
        display: none;
      }

      sup,
      sub {
        font-size: 75%;
        line-height: 0;
      }

      @media (max-width: 720px) {
        .desktop_hide table.icons-inner,
        .social_block.desktop_hide .social-table {
          display: inline-block !important;
        }

        .icons-inner {
          text-align: center;
        }

        .icons-inner td {
          margin: 0 auto;
        }

        .image_block div.fullWidth {
          max-width: 100% !important;
        }

        .mobile_hide {
          display: none;
        }

        .row-content {
          width: 100% !important;
        }

        .stack .column {
          width: 100%;
          display: block;
        }

        .mobile_hide {
          min-height: 0;
          max-height: 0;
          max-width: 0;
          overflow: hidden;
          font-size: 0px;
        }

        .desktop_hide,
        .desktop_hide table {
          display: table !important;
          max-height: none !important;
        }
      }
    </style>
    <!--[if mso
      ]><style>
        sup,
        sub {
          font-size: 100% !important;
        }
        sup {
          mso-text-raise: 10%;
        }
        sub {
          mso-text-raise: -10%;
        }
      </style>
    <![endif]-->
  </head>
  <body
    class="body"
    style="
      background-color: #ffffff;
      margin: 0;
      padding: 0;
      -webkit-text-size-adjust: none;
      text-size-adjust: none;
    "
  >
    <table
      border="0"
      cellpadding="0"
      cellspacing="0"
      class="nl-container"
      role="presentation"
      style="
        mso-table-lspace: 0pt;
        mso-table-rspace: 0pt;
        background-color: #ffffff;
      "
      width="100%"
    >
      <tbody>
        <tr>
          <td>
            <table
              align="center"
              border="0"
              cellpadding="0"
              cellspacing="0"
              class="row row-1"
              role="presentation"
              style="mso-table-lspace: 0pt; mso-table-rspace: 0pt"
              width="100%"
            >
              <tbody>
                <tr>
                  <td>
                    <table
                      align="center"
                      border="0"
                      cellpadding="0"
                      cellspacing="0"
                      class="row-content stack"
                      role="presentation"
                      style="
                        mso-table-lspace: 0pt;
                        mso-table-rspace: 0pt;
                        color: #000000;
                        width: 700px;
                        margin: 0 auto;
                      "
                      width="700"
                    >
                      <tbody>
                        <tr>
                          <td
                            class="column column-1"
                            style="
                              mso-table-lspace: 0pt;
                              mso-table-rspace: 0pt;
                              font-weight: 400;
                              text-align: left;
                              padding-bottom: 10px;
                              padding-left: 10px;
                              padding-right: 10px;
                              padding-top: 10px;
                              vertical-align: top;
                              border-top: 0px;
                              border-right: 0px;
                              border-bottom: 0px;
                              border-left: 0px;
                            "
                            width="100%"
                          >
                            <table
                              border="0"
                              cellpadding="0"
                              cellspacing="0"
                              class="image_block block-1"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                              "
                              width="100%"
                            >
                              <tr>
                                <td
                                  class="pad"
                                  style="
                                    width: 100%;
                                    padding-right: 0px;
                                    padding-left: 0px;
                                    background-color: #ffffff;
                                  "
                                >
                                  <div
                                    align="center"
                                    class="alignment"
                                    style="line-height: 10px"
                                  >
                                    <div
                                      class="fullWidth"
                                      style="max-width: 340px"
                                    >
                                      <img
                                        alt=""
                                        height="auto"
                                        src="https://garbrix.com/padel/assets/images/logo_black_horizontal.png"
                                        style="
                                          display: block;
                                          height: auto;
                                          border: 0;
                                          width: 100%;
                                        "
                                        title=""
                                        width="340"
                                      />
                                    </div>
                                  </div>
                                </td>
                              </tr>
                            </table>
                            <table
                              border="0"
                              cellpadding="10"
                              cellspacing="0"
                              class="divider_block block-2"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                              "
                              width="100%"
                            >
                              <tr>
                                <td class="pad">
                                  <div align="center" class="alignment">
                                    <table
                                      border="0"
                                      cellpadding="0"
                                      cellspacing="0"
                                      role="presentation"
                                      style="
                                        mso-table-lspace: 0pt;
                                        mso-table-rspace: 0pt;
                                      "
                                      width="100%"
                                    >
                                      <tr>
                                        <td
                                          class="divider_inner"
                                          style="
                                            font-size: 1px;
                                            line-height: 1px;
                                            border-top: 0px solid #bbbbbb;
                                          "
                                        >
                                          <span style="word-break: break-word"
                                            > </span
                                          >
                                        </td>
                                      </tr>
                                    </table>
                                  </div>
                                </td>
                              </tr>
                            </table>
                            <table
                              border="0"
                              cellpadding="10"
                              cellspacing="0"
                              class="divider_block block-3"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                              "
                              width="100%"
                            >
                              <tr>
                                <td class="pad">
                                  <div align="center" class="alignment">
                                    <table
                                      border="0"
                                      cellpadding="0"
                                      cellspacing="0"
                                      role="presentation"
                                      style="
                                        mso-table-lspace: 0pt;
                                        mso-table-rspace: 0pt;
                                      "
                                      width="100%"
                                    >
                                      <tr>
                                        <td
                                          class="divider_inner"
                                          style="
                                            font-size: 1px;
                                            line-height: 1px;
                                            border-top: 1px solid #dddddd;
                                          "
                                        >
                                          <span style="word-break: break-word"
                                            > </span
                                          >
                                        </td>
                                      </tr>
                                    </table>
                                  </div>
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
            <table
              align="center"
              border="0"
              cellpadding="0"
              cellspacing="0"
              class="row row-2"
              role="presentation"
              style="mso-table-lspace: 0pt; mso-table-rspace: 0pt"
              width="100%"
            >
              <tbody>
                <tr>
                  <td>
                    <table
                      align="center"
                      border="0"
                      cellpadding="0"
                      cellspacing="0"
                      class="row-content stack"
                      role="presentation"
                      style="
                        mso-table-lspace: 0pt;
                        mso-table-rspace: 0pt;
                        color: #000000;
                        width: 700px;
                        margin: 0 auto;
                      "
                      width="700"
                    >
                      <tbody>
                        <tr>
                          <td
                            class="column column-1"
                            style="
                              mso-table-lspace: 0pt;
                              mso-table-rspace: 0pt;
                              font-weight: 400;
                              text-align: left;
                              padding-bottom: 5px;
                              padding-top: 5px;
                              vertical-align: top;
                              border-top: 0px;
                              border-right: 0px;
                              border-bottom: 0px;
                              border-left: 0px;
                            "
                            width="100%"
                          >
                            <table
                              border="0"
                              cellpadding="10"
                              cellspacing="0"
                              class="heading_block block-1"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                              "
                              width="100%"
                            >
                              <tr>
                                <td class="pad">
                                  <h1
                                    style="
                                      margin: 0;
                                      color: #1e0e4b;
                                      direction: ltr;
                                      font-family: Arial, Helvetica Neue,
                                        Helvetica, sans-serif;
                                      font-size: 38px;
                                      font-weight: 700;
                                      letter-spacing: normal;
                                      line-height: 120%;
                                      text-align: center;
                                      margin-top: 0;
                                      margin-bottom: 0;
                                      mso-line-height-alt: 45.6px;
                                    "
                                  >
                                    ¡Listo! Tu cancha ha sido reservada con
                                    éxito.
                                  </h1>
                                </td>
                              </tr>
                            </table>
                            <table
                              border="0"
                              cellpadding="10"
                              cellspacing="0"
                              class="divider_block block-2"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                              "
                              width="100%"
                            >
                              <tr>
                                <td class="pad">
                                  <div align="center" class="alignment">
                                    <table
                                      border="0"
                                      cellpadding="0"
                                      cellspacing="0"
                                      role="presentation"
                                      style="
                                        mso-table-lspace: 0pt;
                                        mso-table-rspace: 0pt;
                                      "
                                      width="100%"
                                    >
                                      <tr>
                                        <td
                                          class="divider_inner"
                                          style="
                                            font-size: 1px;
                                            line-height: 1px;
                                            border-top: 1px solid #dddddd;
                                          "
                                        >
                                          <span style="word-break: break-word"
                                            > </span
                                          >
                                        </td>
                                      </tr>
                                    </table>
                                  </div>
                                </td>
                              </tr>
                            </table>
                            <div
                              class="spacer_block block-3"
                              style="
                                height: 60px;
                                line-height: 60px;
                                font-size: 1px;
                              "
                            >
                               
                            </div>
                            <table
                              border="0"
                              cellpadding="10"
                              cellspacing="0"
                              class="paragraph_block block-4"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                                word-break: break-word;
                              "
                              width="100%"
                            >
                              <tr>
                                <td class="pad">
                                  <div
                                    style="
                                      color: #444a5b;
                                      direction: ltr;
                                      font-family: Arial, Helvetica Neue,
                                        Helvetica, sans-serif;
                                      font-size: 16px;
                                      font-weight: 400;
                                      letter-spacing: 0px;
                                      line-height: 120%;
                                      text-align: center;
                                      mso-line-height-alt: 19.2px;
                                    "
                                  >
                                    <p style="margin: 0">
                                      Llega con tiempo, calienta y prepárate
                                      para darlo todo en la cancha. 💪🔥
                                    </p>
                                  </div>
                                </td>
                              </tr>
                            </table>
                            <table
                              border="0"
                              cellpadding="10"
                              cellspacing="0"
                              class="paragraph_block block-5"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                                word-break: break-word;
                              "
                              width="100%"
                            >
                              <tr>
                                <td class="pad">
                                  <div
                                    style="
                                      color: #444a5b;
                                      direction: ltr;
                                      font-family: Arial, Helvetica Neue,
                                        Helvetica, sans-serif;
                                      font-size: 25px;
                                      font-weight: 400;
                                      letter-spacing: 0px;
                                      line-height: 120%;
                                      text-align: left;
                                      mso-line-height-alt: 30px;
                                    "
                                  >
                                    <p style="margin: 0; margin-bottom: 16px">
                                      📍 <strong>Ubicación:</strong> '.$clubName.'
                                    </p>
                                    <p style="margin: 0; margin-bottom: 16px">
                                      📅<strong> Fecha:</strong> '.$date.'
                                    </p>
                                    <p style="margin: 0">
                                      ⏰<strong> Hora:</strong> '.$time.'
                                    </p>
                                  </div>
                                </td>
                              </tr>
                            </table>
                            <div
                              class="spacer_block block-6"
                              style="
                                height: 60px;
                                line-height: 60px;
                                font-size: 1px;
                              "
                            >
                               
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
            <table
              align="center"
              border="0"
              cellpadding="0"
              cellspacing="0"
              class="row row-3"
              role="presentation"
              style="
                mso-table-lspace: 0pt;
                mso-table-rspace: 0pt;
                background-color: #f9f8f0;
              "
              width="100%"
            >
              <tbody>
                <tr>
                  <td>
                    <table
                      align="center"
                      border="0"
                      cellpadding="0"
                      cellspacing="0"
                      class="row-content stack"
                      role="presentation"
                      style="
                        mso-table-lspace: 0pt;
                        mso-table-rspace: 0pt;
                        color: #000000;
                        width: 700px;
                        margin: 0 auto;
                      "
                      width="700"
                    >
                      <tbody>
                        <tr>
                          <td
                            class="column column-1"
                            style="
                              mso-table-lspace: 0pt;
                              mso-table-rspace: 0pt;
                              font-weight: 400;
                              text-align: left;
                              padding-bottom: 5px;
                              padding-top: 25px;
                              vertical-align: top;
                              border-top: 0px;
                              border-right: 0px;
                              border-bottom: 0px;
                              border-left: 0px;
                            "
                            width="100%"
                          >
                            <table
                              border="0"
                              cellpadding="10"
                              cellspacing="0"
                              class="heading_block block-1"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                              "
                              width="100%"
                            >
                              <tr>
                                <td class="pad">
                                  <h2
                                    style="
                                      margin: 0;
                                      color: #2b2d2d;
                                      direction: ltr;
                                      font-size: 30px;
                                      font-weight: normal;
                                      letter-spacing: normal;
                                      line-height: 120%;
                                      text-align: left;
                                      margin-top: 0;
                                      margin-bottom: 0;
                                      mso-line-height-alt: 36px;
                                    "
                                  >
                                    <strong>Tu Pago:</strong>
                                  </h2>
                                </td>
                              </tr>
                            </table>
                            <table
                              border="0"
                              cellpadding="0"
                              cellspacing="0"
                              class="paragraph_block block-2"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                                word-break: break-word;
                              "
                              width="100%"
                            >
                              <tr>
                                <td
                                  class="pad"
                                  style="
                                    padding-bottom: 10px;
                                    padding-left: 50px;
                                    padding-right: 50px;
                                    padding-top: 10px;
                                  "
                                >
                                  <div
                                    style="
                                      color: #6f7077;
                                      font-family: Arial, Helvetica Neue,
                                        Helvetica, sans-serif;
                                      font-size: 14px;
                                      line-height: 150%;
                                      text-align: left;
                                      mso-line-height-alt: 21px;
                                    "
                                  >
                                    <p
                                      style="margin: 0; word-break: break-word"
                                    >
                                      <strong>Método de pago:</strong>
                                      Tarjeta/Débito/Crédito
                                    </p>
                                  </div>
                                </td>
                              </tr>
                            </table>
                            <table
                              border="0"
                              cellpadding="10"
                              cellspacing="0"
                              class="table_block block-3"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                              "
                              width="100%"
                            >
                              <tr>
                                <td class="pad">
                                  <table
                                    style="
                                      mso-table-lspace: 0pt;
                                      mso-table-rspace: 0pt;
                                      border-collapse: collapse;
                                      width: 100%;
                                      table-layout: fixed;
                                      direction: ltr;
                                      background-color: transparent;
                                      font-family: Arial, Helvetica Neue,
                                        Helvetica, sans-serif;
                                      font-weight: 400;
                                      color: #000000;
                                      text-align: left;
                                      letter-spacing: 0px;
                                    "
                                    width="100%"
                                  >
                                    <thead
                                      style="
                                        vertical-align: top;
                                        background-color: #eaeaea;
                                        color: #505659;
                                        font-size: 14px;
                                        line-height: 120%;
                                      "
                                    >
                                      <tr>
                                        <th
                                          style="
                                            padding: 10px;
                                            word-break: break-word;
                                            font-weight: 400;
                                            border-top: 1px solid #000000;
                                            border-right: 1px solid #000000;
                                            border-bottom: 1px solid #000000;
                                            border-left: 1px solid #000000;
                                            text-align: left;
                                          "
                                          width="33.333333333333336%"
                                        >
                                          Descripción
                                        </th>
                                        <th
                                          style="
                                            padding: 10px;
                                            word-break: break-word;
                                            font-weight: 400;
                                            border-top: 1px solid #000000;
                                            border-right: 1px solid #000000;
                                            border-bottom: 1px solid #000000;
                                            border-left: 1px solid #000000;
                                            text-align: left;
                                          "
                                          width="33.333333333333336%"
                                        >
                                          Cantidad
                                        </th>
                                        <th
                                          style="
                                            padding: 10px;
                                            word-break: break-word;
                                            font-weight: 400;
                                            border-top: 1px solid #000000;
                                            border-right: 1px solid #000000;
                                            border-bottom: 1px solid #000000;
                                            border-left: 1px solid #000000;
                                            text-align: left;
                                          "
                                          width="33.333333333333336%"
                                        >
                                          <strong>Precio</strong>
                                        </th>
                                      </tr>
                                    </thead>
                                    <tbody
                                      style="
                                        vertical-align: top;
                                        font-size: 14px;
                                        line-height: 120%;
                                      "
                                    >
                                      <tr>
                                        <td
                                          style="
                                            padding: 10px;
                                            word-break: break-word;
                                            border-top: 1px solid #000000;
                                            border-right: 1px solid #000000;
                                            border-bottom: 1px solid #000000;
                                            border-left: 1px solid #000000;
                                          "
                                          width="33.333333333333336%"
                                        >
                                          Renta de Cancha
                                        </td>
                                        <td
                                          style="
                                            padding: 10px;
                                            word-break: break-word;
                                            border-top: 1px solid #000000;
                                            border-right: 1px solid #000000;
                                            border-bottom: 1px solid #000000;
                                            border-left: 1px solid #000000;
                                          "
                                          width="33.333333333333336%"
                                        >
                                          1
                                        </td>
                                        <td
                                          style="
                                            padding: 10px;
                                            word-break: break-word;
                                            border-top: 1px solid #000000;
                                            border-right: 1px solid #000000;
                                            border-bottom: 1px solid #000000;
                                            border-left: 1px solid #000000;
                                          "
                                          width="33.333333333333336%"
                                        >
                                          $'. number_format($priceSubTotal, 2) . '
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </table>
                            <table
                              border="0"
                              cellpadding="10"
                              cellspacing="0"
                              class="table_block block-4"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                              "
                              width="100%"
                            >
                              <tr>
                                <td class="pad">
                                  <table
                                    style="
                                      mso-table-lspace: 0pt;
                                      mso-table-rspace: 0pt;
                                      border-collapse: collapse;
                                      width: 100%;
                                      table-layout: fixed;
                                      direction: ltr;
                                      background-color: transparent;
                                      font-family: Arial, Helvetica Neue,
                                        Helvetica, sans-serif;
                                      font-weight: 400;
                                      color: #000000;
                                      text-align: right;
                                      letter-spacing: 0px;
                                    "
                                    width="100%"
                                  >
                                    <tbody
                                      style="
                                        vertical-align: top;
                                        font-size: 14px;
                                        line-height: 120%;
                                      "
                                    >
                                      <tr>
                                        <td
                                          style="
                                            padding: 10px;
                                            word-break: break-word;
                                            border-top: 0px solid #dddddd;
                                            border-right: 0px solid #dddddd;
                                            border-bottom: 0px solid #dddddd;
                                            border-left: 0px solid #dddddd;
                                          "
                                          width="33.333333333333336%"
                                        >
                                          ​
                                        </td>
                                        <td
                                          style="
                                            padding: 10px;
                                            word-break: break-word;
                                            border-top: 0px solid #dddddd;
                                            border-right: 0px solid #dddddd;
                                            border-bottom: 0px solid #dddddd;
                                            border-left: 0px solid #dddddd;
                                          "
                                          width="33.333333333333336%"
                                        >
                                          IVA:
                                        </td>
                                        <td
                                          style="
                                            padding: 10px;
                                            word-break: break-word;
                                            border-top: 0px solid #dddddd;
                                            border-right: 0px solid #dddddd;
                                            border-bottom: 0px solid #dddddd;
                                            border-left: 0px solid #dddddd;
                                          "
                                          width="33.333333333333336%"
                                        >
                                          $'.number_format($priceIVA, 2).'
                                        </td>
                                      </tr>
                                      <tr>
                                        <td
                                          style="
                                            padding: 10px;
                                            word-break: break-word;
                                            border-top: 0px solid #dddddd;
                                            border-right: 0px solid #dddddd;
                                            border-bottom: 0px solid #dddddd;
                                            border-left: 0px solid #dddddd;
                                          "
                                          width="33.333333333333336%"
                                        >
                                          ​
                                        </td>
                                        <td
                                          style="
                                            padding: 10px;
                                            word-break: break-word;
                                            border-top: 0px solid #dddddd;
                                            border-right: 0px solid #dddddd;
                                            border-bottom: 0px solid #dddddd;
                                            border-left: 0px solid #dddddd;
                                          "
                                          width="33.333333333333336%"
                                        >
                                          <strong>Total</strong>:
                                        </td>
                                        <td
                                          style="
                                            padding: 10px;
                                            word-break: break-word;
                                            border-top: 0px solid #dddddd;
                                            border-right: 0px solid #dddddd;
                                            border-bottom: 0px solid #dddddd;
                                            border-left: 0px solid #dddddd;
                                          "
                                          width="33.333333333333336%"
                                        >
                                          <strong>$'.number_format($priceTotal, 2).'</strong>
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
            <table
              border="0"
              cellpadding="10"
              cellspacing="0"
              class="divider_block block-2"
              role="presentation"
              style="
                mso-table-lspace: 0pt;
                mso-table-rspace: 0pt;
                background-color: #f9f8f0;
              "
              width="100%"
            >
              <tr>
                <td class="pad">
                  <div align="center" class="alignment">
                    <table
                      border="0"
                      cellpadding="0"
                      cellspacing="0"
                      role="presentation"
                      style="mso-table-lspace: 0pt; mso-table-rspace: 0pt"
                      width="100%"
                    >
                      <tr>
                        <td
                          class="divider_inner"
                          style="
                            font-size: 1px;
                            line-height: 1px;
                            border-top: 0px solid #bbbbbb;
                          "
                        >
                          <span style="word-break: break-word"> </span>
                        </td>
                      </tr>
                    </table>
                  </div>
                </td>
              </tr>
            </table>
            <table
              align="center"
              border="0"
              cellpadding="0"
              cellspacing="0"
              class="row row-4"
              role="presentation"
              style="
                mso-table-lspace: 0pt;
                mso-table-rspace: 0pt;
                background-color: #000000;
              "
              width="100%"
            >
              <tbody>
                <tr>
                  <td>
                    <table
                      align="center"
                      border="0"
                      cellpadding="0"
                      cellspacing="0"
                      class="row-content stack"
                      role="presentation"
                      style="
                        mso-table-lspace: 0pt;
                        mso-table-rspace: 0pt;
                        color: #000000;
                        background-color: #000000;
                        width: 700px;
                        margin: 0 auto;
                      "
                      width="700"
                    >
                      <tbody>
                        <tr>
                          <td
                            class="column column-1"
                            style="
                              mso-table-lspace: 0pt;
                              mso-table-rspace: 0pt;
                              font-weight: 400;
                              text-align: left;
                              padding-bottom: 5px;
                              padding-top: 5px;
                              vertical-align: top;
                              border-top: 0px;
                              border-right: 0px;
                              border-bottom: 0px;
                              border-left: 0px;
                            "
                            width="100%"
                          >
                            <table
                              border="0"
                              cellpadding="10"
                              cellspacing="0"
                              class="social_block block-1"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                              "
                              width="100%"
                            >
                              <tr>
                                <td class="pad">
                                  <div align="center" class="alignment">
                                    <table
                                      border="0"
                                      cellpadding="0"
                                      cellspacing="0"
                                      class="social-table"
                                      role="presentation"
                                      style="
                                        mso-table-lspace: 0pt;
                                        mso-table-rspace: 0pt;
                                        display: inline-block;
                                      "
                                      width="36px"
                                    >
                                      <tr>
                                        <td style="padding: 0 2px 0 2px">
                                          <a
                                            href="https://www.instagram.com/padelroomindoor"
                                            target="_blank"
                                            ><img
                                              alt="Instagram"
                                              height="auto"
                                              src="https://garbrix.com/padel/assets/images/instagram_white.png"
                                              style="
                                                display: block;
                                                height: auto;
                                                border: 0;
                                              "
                                              title="instagram"
                                              width="32"
                                          /></a>
                                        </td>
                                      </tr>
                                    </table>
                                  </div>
                                </td>
                              </tr>
                            </table>
                            <table
                              border="0"
                              cellpadding="0"
                              cellspacing="0"
                              class="paragraph_block block-2"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                                word-break: break-word;
                              "
                              width="100%"
                            >
                              <tr>
                                <td
                                  class="pad"
                                  style="
                                    padding-bottom: 10px;
                                    padding-left: 50px;
                                    padding-right: 50px;
                                    padding-top: 10px;
                                  "
                                >
                                  <div
                                    style="
                                      color: #6f7077;
                                      font-family: Arial, Helvetica Neue,
                                        Helvetica, sans-serif;
                                      font-size: 14px;
                                      line-height: 150%;
                                      text-align: center;
                                      mso-line-height-alt: 21px;
                                    "
                                  >
                                    <p
                                      style="margin: 0; word-break: break-word"
                                    >
                                      Has aceptado los
                                      <a
                                        href="https://intelipadel.com/terminosycondiciones/padelroom"
                                        target="_blank"
                                        style="
                                          color: #6f7077;
                                          text-decoration: underline;
                                        "
                                        >Términos y Condiciones</a
                                      >
                                      y el
                                      <a
                                        href="https://intelipadel.com/avisodeprivacidad/padelroom"
                                        target="_blank"
                                        style="
                                          color: #6f7077;
                                          text-decoration: underline;
                                        "
                                        >Aviso de Privacidad</a
                                      >
                                      al confirmar tu pago.
                                    </p>
                                  </div>
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
            <table
              align="center"
              border="0"
              cellpadding="0"
              cellspacing="0"
              class="row row-5"
              role="presentation"
              style="
                mso-table-lspace: 0pt;
                mso-table-rspace: 0pt;
                background-color: #ffffff;
              "
              width="100%"
            >
              <tbody>
                <tr>
                  <td>
                    <table
                      align="center"
                      border="0"
                      cellpadding="0"
                      cellspacing="0"
                      class="row-content stack"
                      role="presentation"
                      style="
                        mso-table-lspace: 0pt;
                        mso-table-rspace: 0pt;
                        color: #000000;
                        background-color: #ffffff;
                        width: 700px;
                        margin: 0 auto;
                      "
                      width="700"
                    >
                      <tbody>
                        <tr>
                          <td
                            class="column column-1"
                            style="
                              mso-table-lspace: 0pt;
                              mso-table-rspace: 0pt;
                              font-weight: 400;
                              text-align: left;
                              padding-bottom: 5px;
                              padding-top: 5px;
                              vertical-align: top;
                              border-top: 0px;
                              border-right: 0px;
                              border-bottom: 0px;
                              border-left: 0px;
                            "
                            width="100%"
                          >
                            <table
                              border="0"
                              cellpadding="0"
                              cellspacing="0"
                              class="icons_block block-1"
                              role="presentation"
                              style="
                                mso-table-lspace: 0pt;
                                mso-table-rspace: 0pt;
                                text-align: center;
                                line-height: 0;
                              "
                              width="100%"
                            >
                              <tr>
                                <td
                                  class="pad"
                                  style="
                                    vertical-align: middle;
                                    color: #1e0e4b;
                                    font-size: 15px;
                                    padding-bottom: 5px;
                                    padding-top: 5px;
                                    text-align: center;
                                  "
                                >
                                  <!--[if vml]><table align="center" cellpadding="0" cellspacing="0" role="presentation" style="display:inline-block;padding-left:0px;padding-right:0px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><![endif]-->
                                  <!--[if !vml]><!-->
                                  <table
                                    cellpadding="0"
                                    cellspacing="0"
                                    class="icons-inner"
                                    role="presentation"
                                    style="
                                      mso-table-lspace: 0pt;
                                      mso-table-rspace: 0pt;
                                      display: inline-block;
                                      padding-left: 0px;
                                      padding-right: 0px;
                                    "
                                  >
                                    <!--<![endif]-->
                                    <tr>
                                      <td
                                        style="
                                          font-size: 15px;
                                          font-weight: undefined;
                                          color: #1e0e4b;
                                          vertical-align: middle;
                                          letter-spacing: undefined;
                                          text-align: center;
                                          line-height: normal;
                                        "
                                      >
                                        Powered by
                                        <a
                                          href="https://intelipadel.com/"
                                          target="_blank"
                                          style="
                                            color: #1e0e4b;
                                            text-decoration: underline;
                                          "
                                          target="_blank"
                                          >intelipadel.com</a
                                        >
                                      </td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
    <!-- End -->
  </body>
</html>
';
        $mail->send();
    } catch (Exception $e) {
        // Handle error if needed
    }
}

if ($method == 'POST') {
    $requestBody = file_get_contents('php://input');
    $params = json_decode($requestBody, true);

    if (isset($params['id_platforms_date_time_slot']) && isset($params['active'])) {
        $id_platforms_date_time_slot = $params['id_platforms_date_time_slot'];
        $active = $params['active'];
        $stripe_id = isset($params['stripe_id']) ? $params['stripe_id'] : null;

        // Update the active column and optionally the stripe_id in the database
        if ($stripe_id) {
            $sql = "UPDATE `platforms_date_time_slots` SET `active` = ?, `stripe_id` = ? WHERE `id_platforms_date_time_slot` = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $active, $stripe_id, $id_platforms_date_time_slot);
        } else {
            $sql = "UPDATE `platforms_date_time_slots` SET `active` = ? WHERE `id_platforms_date_time_slot` = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $active, $id_platforms_date_time_slot);
        }

        if ($stmt->execute()) {
            // Fetch all data from the table
            $stmt->close();
            $sql = "SELECT id_platforms_date_time_slot, id_platforms_field, platforms_date_time_start, platforms_date_time_end, active, stripe_id 
                    FROM platforms_date_time_slots";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $allData = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode($allData);


                if ($active == 1) {
                  $sql = "UPDATE `platforms_date_time_slots` SET `price` = ? WHERE `id_platforms_date_time_slot` = ?";
                  $stmt = $conn->prepare($sql);
                  $stmt->bind_param("di", $params['priceTotal'], $id_platforms_date_time_slot);
                  $stmt->execute();
                }

                // Send confirmation email if active is 1
                if ($active == 1) {
                    // Fetch additional data for the email
                    $sql = "SELECT p.title, a.email, s.id_platforms_field, p.id_platform, s.platforms_date_time_start, s.platforms_date_time_end, f.title as 'cancha'
                            FROM platforms_date_time_slots s 
                            JOIN platforms_fields f ON s.id_platforms_field = f.id_platforms_field 
                            JOIN platforms p ON f.id_platform = p.id_platform 
                            JOIN platforms_users a ON a.id_platforms_user=s.id_platforms_user 
                            WHERE s.id_platforms_date_time_slot = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id_platforms_date_time_slot);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $data = $result->fetch_assoc();
                    $stmt->close();

                    if ($data) {
                        $clubName = $data['cancha'];
                        $email = $data['email'];
                        $datetime =  $data['platforms_date_time_start'];
                        $priceTotal = $params['priceTotal'];
                        $priceIVA = $priceTotal * 0.16; // Assuming 16% IVA
                        $priceSubTotal = $priceTotal - $priceIVA;
                        $date = date('l d \d\e F, Y', strtotime($datetime));
                        setlocale(LC_TIME, 'es_ES.UTF-8');
                        $date = strftime('%A %d de %B, %Y', strtotime($datetime));
                        $time = date('g:i A', strtotime($datetime)) . ' - ' . date('g:i A', strtotime($data['platforms_date_time_end']));

                        sendConfirmationEmail($email, $clubName, $date, $time, $priceSubTotal, $priceIVA, $priceTotal);
                        
                    }
                }
            } else {
                echo json_encode(["message" => "No data found"]);
            }
        } else {
            echo json_encode(["message" => "Failed to update data"]);
        }
    } else {
        echo json_encode(["message" => "Invalid input data"]);
    }
} else {
    echo json_encode(["message" => "Invalid request method"]);
}

$conn->close();
?>