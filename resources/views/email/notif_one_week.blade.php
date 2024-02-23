<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Project {{ $data->title ?? '' }} Expires in 1 week</title>
  
</head>
<body style="background-color: #f4f4f4; font-family: 'Poppins', sans-serif; margin: 0; padding: 0;">
  <div style="max-width: 600px; margin: 2rem auto; border: none; margin-bottom:0px">
    <div style="background-color: #ffffff; padding: 16px; text-align: center; border-top: 8px solid #f0544f;">
      <h1 style="font-family: 'Averia Serif Libre'; font-size: 28px; line-height: 28px; color: #333; margin: 0;">
        {{ $data->user->settingCompany->pluck('field_value','field_title')['name'] ??  '' }}
      </h1>
    </div>
    <div style="background-color: #ffffff; color: #151313; padding: 20px;">
      <h2 style="font-family: 'Averia Serif Libre'; font-size: 24px; color: #333; margin-top: 0px; margin-bottom: 0px; text-align:center;">
        {{ $data->title ?? '' }}
      </h2>
      <h3 style="text-align: center; font-family: 'Averia Serif Libre'; font-size: 22px; margin: 0; margin-top : 10px;">
        <span style="background-color: #E8511E; color: white; padding: 0.2em 0.6em; border-radius: 5px;">
          Expires in 1 week
        </span>
      </h3>
      <p style="font-size: 16px; line-height: 28px; color: #555; margin: 20px 0 10px;">
        Hi <span style="color:#E8511E;">{{ $data->workOrder->quote->customer->director ?? '' }}</span>,
      </p>
      <p style="font-size: 16px; line-height: 28px; color: #555;">
        Your Project has <strong>expired on {{ $data->end_date_email_show->format('M d,Y') ?? '' }}</strong>. Don't miss out on the benefits of our premium features and services.
      </p>
      <div style="text-align: center; margin: 30px 0;">
        <a href="https://api.whatsapp.com/send?phone=6282299988870&text=Hallo,%20saya%20tertarik%20dengan%20%20portfolio%20Thrive%20dari%20Website,%20Ada%20yang%20ingin%20saya%20tanyakan%20terkait" style="background-color: #E8511E; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 50px; font-size: 16px; display: inline-block;">
          Contact Sales
        </a>
      </div>
      <!-- Example Table -->
      <table style="width: 100%; border-collapse: collapse;">
        <tbody>
          <tr>
            <td style="padding: 1px; font-weight: bold; color: #555;">1. Customer:</td>
            <td style="padding: 1px; color: #555;">{{ $data->workOrder->quote->customer->email ?? '' }}</td>
          </tr>
          <tr>
            <td style="padding: 1px; font-weight: bold; color: #555;">2. Expiration Date:</td>
            <td style="padding: 1px; color: #555;">{{ $data->end_date_email_show->format('d M Y') ?? '' }}</td>
          </tr>
          <tr>
            <td style="padding: 1px; font-weight: bold; color: #555;">3. Project:</td>
            <td style="padding: 1px; color: #555;">{{ $data->title ?? '' }}</td>
          </tr>
        </tbody>
      </table>
      <p style="font-size: 16px; line-height: 28px; color: #555; margin-top: 20px;">
        If you wish to renew or make adjustments to your project, <a href="https://api.whatsapp.com/send?phone=6282299988870&text=Hallo,%20saya%20tertarik%20dengan%20%20portfolio%20Thrive%20dari%20Website,%20Ada%20yang%20ingin%20saya%20tanyakan%20terkait" style="color: #E8511E; text-decoration: none;">reach out to our sales team</a>.
      </p>
    </div>
    <div style="background-color: #ffffff; text-align: center; padding: 20px; font-size: 12px; color: #949494;">
      <h1 style="font-family: 'Averia Serif Libre'; font-size: 28px; color: rgba(148, 148, 148, 1); margin: 0;">
        {{ $data->user->settingCompany->pluck('field_value','field_title')['name'] ??  '' }}
      </h1>
    </div>
    <div  style="border-top: 8px solid #f0544f; background-color: #ffffff !important;"></div>
  </div>
</body>
</html>
