<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>{'Add Existing Files'|gettext}  |  Exponent CMS</title>
    <link rel="stylesheet" type="text/css" href="{$smarty.const.URL_FULL}framework/core/assets/css/msgq.css" />
    <link rel="stylesheet" type="text/css" href="{$smarty.const.URL_FULL}framework/core/assets/css/button.css" />
    <link rel="stylesheet" type="text/css" href="{$smarty.const.URL_FULL}framework/core/assets/css/tables.css" />
    <link rel="stylesheet" type="text/css" href="{$smarty.const.URL_FULL}framework/core/assets/css/common.css" />
    <link rel="stylesheet" type="text/css" href="{$smarty.const.URL_FULL}framework/core/assets/css/admin-global.css" />
    <link rel="stylesheet" type="text/css" href="{$smarty.const.URL_FULL}framework/modules/file/assets/css/filemanager.css" />

    <script type="text/javascript" src="{$smarty.const.URL_FULL}exponent.js.php"></script>
    <script type="text/javascript" src="{$smarty.const.YUI3_PATH}yui/yui-min.js"></script>
</head>
<body class="exp-skin">
<div id="exp-adder">
    <h1>{"Add Existing Files"|gettext}</h1>
    <div id="actionbar">
        <a id="backlink" class="back awesome small green" href="{link action=picker ajax_action=1 ck=$smarty.get.ck update=$smarty.get.update fck=$smarty.get.fck}{if $smarty.const.SEF_URLS}?{else}&{/if}CKEditor={$smarty.get.CKEditor}&CKEditorFuncNum={$smarty.get.CKEditorFuncNum}&langCode={$smarty.get.langCode}"><span>{'Back to Manager'|gettext}</span></a>
    </div>
	<div class="info-header clearfix">
		<div class="related-actions">
			{help text="Get Help"|gettext|cat:" "|cat:("Deleting Files"|gettext) module="add-files"}
		</div>
        <p>{"Select the following files found on the server to add them to the File Manager."|gettext}</p>
	</div>
    {messagequeue}

    <div id="filelist">
    {form action=addit}
      <table id="filenames" class="exp-skin-table">
        <thead>
    	   <tr>
               <th><a href="#" onclick="files_selectUnselectAll(true); return false;">{'All'|gettext}</a>&nbsp;|&nbsp;<a href="#" onclick="files_selectUnselectAll(false); return false;">{'None'|gettext}</a></th>
               <th>{'Filename'|gettext}</th>
               <th>{'Folder'|gettext}</th>
           </tr>
    	</thead>
    	<tbody>
{foreach from=$files item=file key=src}
        <tr class="{cycle values="even,odd"}">
            <td width="20">
                {control type="checkbox" name="addit[]" value=$src}
            </td>
            <td>
                {$file}
            </td>
            <td>
                {$src}
            </td>
        </tr>
{foreachelse}
        <tr><td colspan=3>{'There don\'t appear to be any files on the server which aren\'t already in the File Manager'|gettext}</td></tr>
{/foreach}
        </tbody>
     </table>
    {control type=buttongroup submit="Add Selected Files"|gettext}
    {/form}
    </div>
</div>
<script type="text/javascript">
function files_selectUnselectAll(setChecked) {
	var elems = document.getElementsByTagName("input");
	for (var key = 0; key < elems.length; key++) {
		if (elems[key].type == "checkbox" && elems[key].name.substr(0,6) == "addit[") {
			elems[key].checked = setChecked;
		}
	}
}
</script>
</body>
</html>
