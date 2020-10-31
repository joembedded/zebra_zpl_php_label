# ZEBRA Label - API based ZPL printer in PHP #
**for ZEBRA label printers**

This small PHP script 'zebra_printer.php' prints an indivial label on demand.
It is possible to include own images and variables.

A ZPL printer with LAN is required. Here, a 'ZD420-300dpi' was used with 38x23 mm labels (1.5" x 0.9")

Steps:
- Install the printer locally (e.g. USB)
- Install ZEBRA Printer Setup Utility
- Configure printer to 'wired' and configure to fixed IP
- By default TCP port 6101 is used for 'RAW' input
- The printer offers also a webserver (for adjustment and calibration)
- Install the scripts on a local webserver or NAS. PHP is required
- To show how 'zebra_printer.php'is working, start 'zebra_label.php'
- Feel free to add own '.frm'-files
- http://labelary.com/viewer.html offers an excellent online tool to edit own forms


![The result](https://github.com/joembedded/zebra_zpl_php_label/blob/master/docu/labels.jpg)

[Image: The result]


## Changelog ##
- V0.1  First Release

---


