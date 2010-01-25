<?php
/*

Copyright (c) 2009, SilverStripe Australia PTY LTD - www.silverstripe.com.au
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software
      without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY
OF SUCH DAMAGE.
*/

/**
 * A Form field which can be used to display a button which will
 * trigger a pixlr editor to appear. 
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class PixlrEditorField extends FormField
{
	/**
	 * Set this to true if you have created a top level crossdomain.xml file. There's an example in
	 * the module's directory if you wish to use that.
	 *
	 * Additionally, if you are running on a lan or local network with no ability
	 * for pixlr to request the image publicly from your server over the internet,
	 * leave this false and the image will be transferred to pixlr instead.
	 *
	 * @var boolean
	 */
	public static $use_credentials = false;

	private $label;

	private $targetUrl;
	private $exitUrl;
	private $returnParams;

	/**
	 *
	 * @param String $name
	 * @param String $title
	 * @param Image $value
	 * @param array $params
	 *			Any additional parameters that should be sent back to
	 *			the save handler
	 * @param String $targetUrl
	 *			The URL that pixlr returns data back to
	 * @param String $exitUrl
	 *			The URL called if pixlr is closed without saving any data
	 */
    public function __construct($name, $title = '', $value = '', $returnParams = array(), $targetUrl = '', $exitUrl = '')
	{
		$this->label = $title;
		$this->targetUrl = $targetUrl;
		$this->exitUrl = $exitUrl;
		$this->returnParams = $returnParams;
		
		parent::__construct($name, '', $value);
	}

	public function Field()
	{
		Requirements::javascript('pixlr/javascript/pixlr.js');
		Requirements::javascript('pixlr/javascript/pixlr.jquery.js');

		$fieldAttributes = array(
			'id' => $this->id(),
			'class' => 'pixlrTrigger',
			'value' => Convert::raw2att($this->label),
			'type' => 'button',
		);

		$targetUrl = strlen($this->targetUrl) ? $this->targetUrl : Director::absoluteURL('pixlr/saveimage');
		$exitUrl = strlen($this->exitUrl) ? $this->exitUrl : Director::absoluteURL('pixlr/closepixlr');

		// now add any additional parameters onto the end of the target string
		$sep = strpos($targetUrl, '?') ? '&amp;' : '?';

		foreach ($this->returnParams as $param => $v) {
			$targetUrl .= $sep . $param . '=' . $v;
			$sep = '&amp;';
		}

		$opts = array(
			'referrer' => Convert::raw2js('SilverStripe CMS'),
			// 'loc' => Member::currentUser()->
			'title' => $this->value ? Convert::raw2js($this->value->Name) : 'New Image',
			'locktarget' => 'true',
			'exit' => $exitUrl,
			'target' => $targetUrl,
			'method' => 'get',
		);

		// where should we open the editor? as in, which window is it rooted in? 
		$openin = 'window';

		if ($this->value) {
			if (self::$use_credentials) {
				$opts['credentials'] = 'true';
				$opts['image'] = Convert::raw2js(Director::absoluteBaseURL() . $this->value->Filename);
			} else {
				// need to post the image to their server first, so we'll
				// base64_encode it now and stick the raw data into the page. We don't want to do
				// the actual post yet though, because it might not actually be used til the
				// client wants to start the editing
				// @TODO - revisit this? 
				$opts['id'] = $this->value->ID;
				$opts['preload'] = Director::absoluteURL('pixlr/sendimage');

				$openin = 'window.parent.parent';
			}

			$opts['locktitle'] = 'true';
			
			$opts['mode'] = 'popup';
		}

		$opts = Convert::raw2json($opts);

		$script = <<<JSCRIPT
jQuery().ready(function () {
var opts = $opts;
opts.openin = $openin;
jQuery('#{$this->id()}').pixlrize(opts);
});
JSCRIPT;

		Requirements::customScript($script, 'pixlr-'.$this->id());

		return $this->createTag('input', $fieldAttributes);
	}

}
?>