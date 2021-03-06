<?php
# default settings
$width = !empty($width) ? $width : 320;
$height = !empty($height) ? $height : 240;
$quality = !empty($quality) ? $quality : 'high';
$showVolume = !empty($showVolume) ? $showVolume : 1;
$uuid = !empty($uuid) ? $uuid : substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789',20)),0,40);$offset = !empty($offset) ? $offset : 0; ?>

<?php echo $this->Html->script('/file_manager/record/AC_OETags'); ?>
<!--  BEGIN Browser History required section -->
<?php echo $this->Html->script('/file_manager/record/history/history'); ?>
<!--  END Browser History required section -->
<script language="JavaScript" type="text/javascript">
<!--
// -----------------------------------------------------------------------------
// Globals
// Major version of Flash required
var requiredMajorVersion = 9;
// Minor version of Flash required
var requiredMinorVersion = 0;
// Minor version of Flash required
var requiredRevision = 28;
// -----------------------------------------------------------------------------
// -->
</script>
<script language="JavaScript" type="text/javascript">
<!--
// Version check for the Flash Player that has the ability to start Player Product Install (6.0r65)
var hasProductInstall = DetectFlashVer(6, 0, 65);

// Version check based upon the values defined in globals
var hasRequestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);

if ( hasProductInstall && !hasRequestedVersion ) {
	// DO NOT MODIFY THE FOLLOWING FOUR LINES
	// Location visited after installation is complete if installation is required
	var MMPlayerType = (isIE == true) ? "ActiveX" : "PlugIn";
	var MMredirectURL = window.location;
    document.title = document.title.slice(0, 47) + " - Flash Player Installation";
    var MMdoctitle = document.title;

	AC_FL_RunContent( 'id','red5recorder','width', <?php echo $width; ?>,'height', <?php echo $height; ?>,'codebase','http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab','movie','red5recorder','quality','<?php echo $quality; ?>','bgcolor','#869ca7','allowscriptaccess','sameDomain' ); //end AC code
} else if (hasRequestedVersion) {
	// if we've detected an acceptable version
	// embed the Flash Content SWF when all tests are passed
	AC_FL_RunContent(
			"src", "/file_manager/record/red5recorder",
			"FlashVars", "server=rtmp://razorit.com:1935/oflaDemo/&fps=15&quality=70&fileName=<?php echo $uuid; ?>&keyFrame=6&MMredirectURL="+MMredirectURL+'&MMplayerType='+MMPlayerType+'&MMdoctitle='+MMdoctitle+"&width=<?php echo $width; ?>&height=<?php echo $height; ?>&showVolume=<?php echo $showVolume; ?>",			
			"width", <?php echo $width; ?>,
			"height", <?php echo $height; ?>,
			"align", "middle",
			"id", "red5recorder",
			"quality", "<?php echo $quality; ?>",
			"bgcolor", "#869ca7",
			"name", "red5recorder",
			"allowScriptAccess","sameDomain",
			"type", "application/x-shockwave-flash",
			"pluginspage", "http://www.adobe.com/go/getflashplayer"
	);
  } else {  // flash is too old or we can't detect the plugin
    var alternateContent = 'Alternate HTML content should be placed here. '
  	+ 'This content requires the Adobe Flash Player. '
   	+ '<a href=http://www.adobe.com/go/getflash/>Get Flash</a>';
    document.write(alternateContent);  // insert non-flash content
  }
// -->

function startRecording() {
 	document.getElementById("startRecording").startRecording(); 
	// start the recording of the video 
	return false;
}
  </script>

<noscript>
<object 
	classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
	id="red5recorder" width="<?php echo $width; ?>" height="<?php echo $height; ?>"
	codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
	  <param name="movie" value="/file_manager/record/red5recorder.swf" />
	  <param name="quality" value="<?php echo $quality; ?>" />
	  <param name="bgcolor" value="#869ca7" />
	  <param name="allowScriptAccess" value="sameDomain" />
	  <embed src="/file_manager/record/red5recorder.swf" quality="<?php echo $quality; ?>" bgcolor="#869ca7"
			width="<?php echo $width; ?>" height="<?php echo $height; ?>" name="red5recorder" align="middle"
			play="true"
			loop="false"
			quality="<?php echo $quality; ?>"
			allowScriptAccess="sameDomain"
			type="application/x-shockwave-flash"
			pluginspage="http://www.adobe.com/go/getflashplayer">
	  </embed>
</object>
</noscript>

<?php 
if ($hasMany === true) {
  if ($form !== false) { echo $this->Form->create('Media', array('type' => 'file')); }
    echo $this->Form->hidden('Media.'.$offset.'.title', array('value' => ''));
    echo $this->Form->hidden('Media.'.$offset.'.description', array('value' => ''));
    echo $this->Form->hidden('Media.'.$offset.'.uuid', array('value' => $uuid));
    echo $this->Form->hidden('Media.'.$offset.'.user_id', array('value'=> $this->Session->read('Auth.User.id')));
    echo $this->Form->hidden('Media.'.$offset.'.type', array('value' => 'record'));
    echo $this->Form->hidden('Media.'.$offset.'.model', array('value' => $model));
    #echo $this->Form->hidden('Media.foreign_key', array('value' => $foreignKey));
  if ($form !== false) { echo $this->Form->end('Submit Recording'); }
} else {
   if ($form !== false) { echo $this->Form->create('Media', array('type' => 'file')); }
    echo $this->Form->hidden('Media.title', array('value' => ''));
    echo $this->Form->hidden('Media.description', array('value' => ''));
    echo $this->Form->hidden('Media.uuid', array('value' => $uuid));
    echo $this->Form->hidden('Media.user_id', array('value'=> $this->Session->read('Auth.User.id')));
    echo $this->Form->hidden('Media.type', array('value' => 'record'));
    echo $this->Form->hidden('Media.model', array('value' => $model));
    #echo $this->Form->hidden('Media.foreign_key', array('value' => $foreignKey));
  if ($form !== false) { echo $this->Form->end('Submit Recording'); }
}