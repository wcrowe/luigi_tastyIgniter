# Printer

This extension enables printing of orders to thermal printers from TastyIgniter. It supports USB (ESC/POS), IP/Network Printing (ESC/POS) and Epson ePOS network web service. You can add multiple printers per location and can limit what is printed to each device by category selection.


## Usage

After installation a 'print docket' button will be added to the order view. There will also be the ability to add printers in Tools -> Printers, as well as modify global receipt formats in Settings.

The extension also enables autoprinting of new orders as they arrive either through a dedicated autoprint page, or optionally anywhere in the admin panel.

## Configuration

The extension supports printers connected by:
1. USB
2. Network or IP
3. The Epson ePOS network web service

To start, plug in the printer, connect to your computer or tablet and follow the manufacturer's setup instructions.


### USB printer

This mode is only supported on browsers that support [WebUSB](https://caniuse.com/#feat=webusb) and on connections running over HTTPS. Make sure the browser you are using supports WebUSB and the installation is behind a secure certificate. 

On the add printer page, select "USB" as the type, and then choose "Setup printer", select the device from the list and choose connect. Save your printer settings.

#### If the device is not present in the list

If you are on a Windows device and the printer is not available in the list, you need to force the system to use the WinUSB driver for the device. 

The easiest way to do this is to install [Zadig](https://zadig.akeo.ie), select the device, choose WinUSB as the driver and select reinstall.


### IP/Ethernet printer

Your printer should be connected to the same network as the computer managing the orders.

IP printers require the [proxy application](https://github.com/thoughtco/ti-print-proxy/releases) to be running so that websocket connections are converted to TCP packets and sent to your printer. Please install and run this program when you want to enable IP printing.

On the add printer screen in TastyIgniter choose type as IP/Network and enter the IP address of ***the computer running the proxy application*** (note: this can be a local IP such as 192.168.0.1). Enter the port you want the proxy application to listen for connections on.

In the proxy application, enter the same listening port as above, and also enter ***the printer's IP address*** (note: this can be a local IP) and the port it is listening on (usually 9100, but check with your hardware manufacturer for support). Start the proxy application - it must always be running for the extension to work.

If you are using multiple printers you may be interested [this command line application](https://github.com/BreakSecurity/ti-printers-proxy)


#### SSL connections

If your TastyIgniter instance is running over SSL you will need to add a self-signed SSL certificate to the proxy application, and to the trust store on the computer making the connection. Without this, the browser will display an insecure content warning.

Ensure your key is NOT encrypted and has no password associated with it, or the proxy application will not work.

The following command creates a self-signed certicate and key for localhost for 365 days. If you use it please ensure you do not enter a passphrase on the private key.

```
openssl req -x509 -days 365 -out localhost.crt -keyout localhost.key \
  -newkey rsa:2048 -nodes -sha256 \
  -subj '/CN=localhost' -extensions EXT -config <( \
   printf "[dn]\nCN=localhost\n[req]\ndistinguished_name = dn\n[EXT]\nsubjectAltName=DNS:localhost\nkeyUsage=digitalSignature\nextendedKeyUsage=serverAuth")
```

Alternatively use [this website](https://certificatetools.com) and ensure you choose the self-signed CSR option, before downloading the private key and PEM certificates that are generated.

Enter the certificate and key into the appropriate fields in the proxy application and start the server.

Now you will need to install the certificate to your browser/computer trust store. On a mac, simply double click on the certificate add add it to Keychain.

On Windows Edge: Export the cert and click on it. Click install certificate. Click “Place all certificates in the following store”, and then click “Browse”. Inside the dialog box, click “Trusted Root Certification Authorities”, and then click “OK”.

In Windows Chrome: Click on Settings -> Advanced settings -> More -> Manage certificates Click servers -> import -> select the certificate you exported.

If you are using Firefox, [follow instructions here](https://www.starnet.com/xwin32kb/installing-a-self-signed-certificate-on-firefox/)


### EPSON ePOS network printer

Some recent EPSON printers come bundled with an ePOS SDK for JavaScript allowing connections from a browser ([see here for information and hardware support](https://download.epson-biz.com/modules/pos/index.php?page=soft&scat=57)). 

If your device is supported, once you plug the printer in for the first time, it should print a docket with its IP address and some other details on it. Keep this as you’ll need the information later in the setup.

#### Utility software and settings

Then download and install the appropriate utility software for the model (e.g. TM20-III)

Open the utility software and if the printer doesnt appear, select “add port”.

Within the new window select “Ethernet” and then search. Your printer should appear in the list, in which case select it and press “OK”. If not, enter the IP address on the docket into the boxes and select OK.

The printer should appears in the original screen - select it and press “OK”, and a new window will load with the printer's settings.

Select Network > Basic Settings -> IP Address. Set to manual, using the same IP address on the docket. This is in order to permanently set the IP address to be the one on the print out, so that it doesn’t change and render the SSL certificate invalid.

Go to Network > Detailed Settings > ePOS-Print. Enable ePOS print and make a note of the printer name (usually local_printer).

Go to Network -> Detailed Settings > Certificate and click on self signed cert. In the modal that opens enter the IP address for the common name. Set a validity period of 3 years. Click OK, then Set on the window you are returned to.

Browse to the IP address in Chrome or Edge. You will be given an insecure warning so click proceed any way. Click on the not secure warning in the address bar, click on certificate details and export.

Now you will need to install the certificate to your browser/computer trust store. On a mac, simply double click on the certificate add add it to Keychain.

On Windows Edge: Export the cert and click on it. Click install certificate. Click “Place all certificates in the following store”, and then click “Browse”. Inside the dialog box, click “Trusted Root Certification Authorities”, and then click “OK”.

In Windows Chrome: Click on Settings -> Advanced settings -> More -> Manage certificates Click servers -> import -> select the certificate you exported.

If you are using Firefox, [follow instructions here](https://www.starnet.com/xwin32kb/installing-a-self-signed-certificate-on-firefox/)

Browse to the IP address to ensure it has picked up the new certificate.

#### System settings

In the web app to go Tools -> Printer and click on add to add a printer. Add the IP address from the receipt slip, the port should be 8008 for non SSL connections and 8043 for SSL connections and the device name should be local_printer (unless you changed it in the configuration).

Ensure you have a docket format set up in Settings -> Printer.

Go to an order and click “print docket”. The order will print and automatically be marked as complete.



### Receipt format

The receipts are formatted in a markdown-like format and converted to the corresponding code for the printer you select.

> Heading 1 (#) is converted to text height 3, bold and aligned center (unless a previous alignment command has been found)

> Heading 2 (##) is converted to text height 2, bold and aligned center (unless a previous alignment command has been found)

> Heading 3 (###) is converted to text height 1, bold and aligned center (unless a previous alignment command has been found)

> All other headings (#### ...) are text height 1 and aligned center (unless a previous alignment command has been found)

> Paragraph styles are text height 1 and aligned left (unless a previous alignment command has been found)

> Lines beginning ***** are assumed to be horizontal rules and a center aligned series of dashes is printed

> Lines beginning >>>>> are assumed to be a request for a cut in the page

> Lines in the format [img 32,32] allow you to print an image stored on the printer at the given key code, for example [img 48,42] will print keycode 48, 42. Images will be centered (unless a previous alignment command has been found)

> Lines in the format [qrcode 3,https://yoururl.com] allow you to print an QR code with a link to the url. The first parameter is a size between 1 and 5, with 1 being the smallest. The QR will be centered (unless a previous alignment command has been found)

> Blank lines are treated as a feed line request

> Lines beginning <| will begin left alignment, all lines will be affected until a new alignment command is found

> Lines beginning |> will begin right alignment, all lines will be affected until a new alignment command is found

> Lines beginning || will begin center alignment, all lines will be affected until a new alignment command is found



### Default receipt

The default receipt added on install is:

```blade
||# {{ $location_name }}
### {{ $location_address }}
### Phone: {{ $location_telephone }}
-----
## Order for {{ $order_type }}
## Order number: {{ $order_id }}
## {{ $order_date }} {{ $order_time }}
## {{ $order_payment }}
-----
<|{{ $customer_name }}
{{ $telephone }}
{{ $order_address }}
-----
@foreach ($order_menus as $menu)
### {!! str_pad(substr($menu['menu_quantity'].'x '.$menu['menu_name'], 0, $charsPerRow - 11), $charsPerRow - 11, ' ', STR_PAD_RIGHT) !!}    {{ str_pad($menu['menu_price'], 7, ' ', STR_PAD_LEFT) }}
@if ($menu['menu_options'])@foreach ($menu['menu_options'] as $option){!! str_pad(substr($option['menu_option_linequantity'].'x '.$option['menu_option_name'], 0, $charsPerRow - 11), $charsPerRow - 11, ' ', STR_PAD_RIGHT) !!}    {{ str_pad($option['menu_option_linetotal'], 7, ' ', STR_PAD_LEFT) }}
@endforeach 
@endif

@endforeach
-----
@foreach ($order_totals as $total)
### {{ str_pad(substr($total['order_total_title'], 0, $charsPerRow - 11), $charsPerRow - 11, ' ', STR_PAD_RIGHT) }}    {{ str_pad($total['order_total_value'], 7, ' ', STR_PAD_LEFT) }}
@endforeach
-----
### {{ $site_url }}
```
