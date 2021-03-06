# Pixlr Image Editor Module

## Maintainer Contact

Marcus Nyeholt <marcus (at) silverstripe (dot) com (dot) au>

## Requirements

SilverStripe 2.4.x
CURL
VersionedFiles (optional)

It is recommended you use the VersionedFiles module to ensure that any
changes you make while editing the image can be rolled back to a previous
copy. 

Documentation
------------------------------------------------------------------------------
* Copy the pixlr folder to the root of your silverstripe instance and run
  dev/build
* If you are running a publicly accessible website, you will get a noticeable
  speed increase by using a crossdomain.xml (a sample is provided in the
  module subdirectory) at the root of your website so that the pixlr
  application can directly access the image files it needs. Make sure to set
  PixlrEditorField::$use_credentials to TRUE in your mysite/_config.php

Further information can be found at 
http://wiki.github.com/nyeholt/silverstripe-pixlr/

API
------------------------------------------------------------------------------

* PixlrEditorField::$use_credentials = true|false
  Indicates whether the pixlr editor can access content directly from the
  website without needing additional server requests to send data around.
  Recommended. 

Troubleshooting
------------------------------------------------------------------------------

For additional help, create issues at
http://github.com/nyeholt/silverstripe-pixlr/issues