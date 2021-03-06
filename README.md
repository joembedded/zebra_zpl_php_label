# ZEBRA Label - API based ZPL printer in PHP #
**for ZEBRA label printers**

This small PHP script 'zebra_printer.php' prints an indivial label on demand.
It is possible to include own images and variables.

ZPL is a simple language for lables. A 'Programming Guide' can be downloaded from the manufacturer's homepage.
ZPL files can be edited manually or made with the (free) ZEBRA designer.


This is a simple label with 4 lines
```
	^XA
	^FX Simple Label (e.g. Adress) 38x23mm, 4 Lines
	^FB460,5,,C,0
	^FO0,50^CF0,40
	^FD
	My Name\&
	Street\&
	12345 City\&
	joembedded@gmail.com\&
	^FS
	^XZ
```

A ZPL printer with LAN is required. Here, a 'ZD420-300dpi' was used with 38x23 mm labels (1.5" x 0.9")

Steps:
- Install the printer locally (e.g. USB)
- Install ZEBRA Printer Setup Utility
- Configure printer to 'wired' and configure to fixed IP
- By default TCP port 6101 is used for 'RAW' input
- The printer offers also a webserver (for adjustment and calibration)
- Install the scripts from this project on a local webserver. PHP is required
- To show how 'zebra_printer.php' is working, start 'zebra_label.php'
- Feel free to add own '.frm'-files
- http://labelary.com/viewer.html offers an excellent and free online tool to edit own forms
- Also very easy: design a label with the (free) ZEBRA Designer and save the output (ZPL) to a file ('.prn') and then edit it

![The result](https://github.com/joembedded/zebra_zpl_php_label/blob/main/docu/labels.jpg)

[Image: The result]


## Changelog ##
- V0.1  First Release
- V0.2  Added 'badge.html' as sample application form

---


