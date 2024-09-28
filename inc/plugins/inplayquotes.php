<?php
// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.");
}

// ACP
$plugins->add_hook("admin_formcontainer_output_row", "inplayquotes_permission");
$plugins->add_hook("admin_user_groups_edit_commit", "inplayquotes_permission_commit");

// postbit
$plugins->add_hook("postbit", "inplayquotes_postbit");

// Alerts

if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
    $plugins->add_hook("global_start", "inplayquotes_alerts");
}

// misc
$plugins->add_hook('misc_start', 'inplayquotes_misc');

// global
$plugins->add_hook('global_start', 'inplayquotes_global');

// index
$plugins->add_hook('global_intermediate', 'inplayquotes_index');

// forumbits
$plugins->add_hook('build_forumbits_forum ', 'inplayquotes_forumbit_forum');

// profile
$plugins->add_hook('member_profile_end', 'inplayquotes_member_profile');

function inplayquotes_info()
{
    return array(
        "name" => "Inplayzitate",
        "description" => "Gibt die Möglichkeit, das User Inplayzitate einschicken können.",
        "website" => "https://github.com/Ales12/inplayquotes",
        "author" => "Ales",
        "authorsite" => "https://github.com/Ales12",
        "version" => "1.0",
        "guid" => "",
        "codename" => "",
        "compatibility" => "*"
    );
}

function inplayquotes_install()
{

    global $db, $cache, $mybb;

    // normale Datenbank erstellen
    if ($db->engine == 'mysql' || $db->engine == 'mysqli') {
        $db->query("CREATE TABLE `" . TABLE_PREFIX . "inplayquotes` (
          `qid` int(10) NOT NULL auto_increment,
          `uid` int(10) NOT NULL,
          `tid` int(10) NOT NULL,
          `pid` int(10) NOT NULL,
          `quote` varchar(5000)  CHARACTER SET utf8 NOT NULL,
          PRIMARY KEY (`qid`)
        ) ENGINE=MyISAM" . $db->build_create_table_collation());
    }

    // Usergruppe, einstellungen (Wer kann ein Zitat einreichen)

    if (!$db->field_exists("canquoteinplay", "usergroups")) {
        switch ($db->type) {
            case "pgsql":
                $db->add_column("usergroups", "canquoteinplay", "smallint NOT NULL default '1'");
                break;
            default:
                $db->add_column("usergroups", "canquoteinplay", "tinyint(1) NOT NULL default '1'");
                break;

        }
    }

    $cache->update_usergroups();

    // Einstellungen
    $setting_group = array(
        'name' => 'inplayquotes',
        'title' => 'Einstellungen für Inplayzitate',
        'description' => "Hier kannst du die Einstellungen für die Inplayzitate vornehmen",
        'disporder' => 5, // The order your setting group will display
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);


    $setting_array = array(
        // A text setting
        'iq_selectforum' => array(
            'title' => "Inplayforen",
            'description' => "Wähle hier die Inplayforen aus",
            'optionscode' => 'forumselect',
            'value' => '1', // Default
            'disporder' => 1
        ),
        // A select box
        'iq_withava' => array(
            'title' => "Zitate mit Avatar/Icon",
            'description' => "Soll neben den Zitaten ein Avatar/Icon angezeigt werden?",
            'optionscode' => 'yesno',
            'value' => 0,
            'disporder' => 2
        ),
        // A select box
        'iq_avatar' => array(
            'title' => "Anzeige des Avatars",
            'description' => "Soll bei den Zitaten ein Avatar angezeigt werden?",
            'optionscode' => 'yesno',
            'value' => 0,
            'disporder' => 3
        ),
        'iq_profilfield' => array(
            'title' => "Anzeige mit Profilfeld",
            'description' => "Soll anstatt dem Avatar ein Profilfeld angezeigt werden? <b>Nur wenn Avatar auf Nein!</b>",
            'optionscode' => 'yesno',
            'value' => 0,
            'disporder' => 4
        ),
        'iq_get_profilfield' => array(
            'title' => "FID des Profilfelds",
            'description' => "Gebe hier an, welches FID das Profilfeld hat, welches anstatt das Avatar angezeigt werden soll.",
            'optionscode' => 'text',
            'value' => "fid1",
            'disporder' => 5
        ),
    );

    foreach ($setting_array as $name => $setting) {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

    // templates


    $insert_array = array(
        'title' => 'inplayquotes_index',
        'template' => $db->escape_string('<tr>
<td class="tcat"><span class="smalltext"><strong>{$lang->iq_index}</strong></span></td>
</tr>
<tr>
<td class="trow1">
	{$inplayquotes_index_bit}
	<div class="inplayquotes_index_goto smalltext"><a href="misc.php?action=inplayquotes">{$lang->iq_index_goto}</a></div>
    </td>
</tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'inplayquotes_index_bit',
        'template' => $db->escape_string('<div class="inplayquotes_index_by">{$quote_by}</div>
<div class="inplayquotes_index_quote smalltext">{$quote}</div>
<div class="inplayquotes_index_outof smalltext">{$quote_outof}</div>
'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'inplayquotes_misc',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->iq_misc}</title>
{$headerinclude}
</head>
<body>
{$header}
<table class="tborder" border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}">
	<tr><td class="thead"><strong>{$lang->iq_misc}</strong></td></tr>
<tr><td class="trow1"><div class="inplayquotes_misc_flex">
	{$inplayquotes_misc_bit}
	</div>	</td></tr>	</table>
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'inplayquotes_misc_bit',
        'template' => $db->escape_string('<div class="inplayquotes_misc_box">{$option}
	<div class="inplayquotes_misc_quote">{$quote}</div>
	<div class="inplayquotes_misc_info">{$quote_by} {$quote_outof}</div>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'inplayquotes_misc_bit_avatar',
        'template' => $db->escape_string('<div class="inplayquotes_misc_avatar"><img src="{$avatar}"></div>
<div class="inplayquotes_misc_avatar_box">{$option}
	<div class="inplayquotes_misc_quote">{$quote}</div>
	<div class="inplayquotes_misc_info">{$quote_by} {$quote_outof}</div>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'inplayquotes_misc_option',
        'template' => $db->escape_string('<div class="float_right">
	<a href="misc.php?action=inplayquotes&deletequote={$quotes[\'qid\']}" title="Zitat löschen">{$lang->iq_misc_delete}</a>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'inplayquotes_postbit',
        'template' => $db->escape_string('<a onclick="$(\'#iq_{$post[\'pid\']}\').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== \'undefined\' ? modal_zindex : 9999) }); return false;" style="cursor: pointer;" class="postbit_quote postbit_mirage">	<span>
{$lang->iq_postbit_quotes}</span></a>	
<div class="modal" id="iq_{$post[\'pid\']}" style="display: none;">
<form action="misc.php?action=add_quote" method="post" id="new_quote">
	<div class="inplayquotes_box">
		<div class="inplayquotes_subject">{$lang->iq_quote_outof}</div>
		<div class="inplayquotes_from">{$lang->iq_quote_from}</div>
		<input name="uid" value="{$post[\'uid\']}" type="hidden"> <input name="tid" value="{$post[\'tid\']}" type="hidden"> <input name="pid" value="{$post[\'pid\']}" type="hidden">
		<div class="inplayquotes_quote">{$lang->iq_quote}</div>
		<div class="inplayquotes_textarea"><textarea name="inplayquote" class="textarea" id="inplayquote" rows="3" cols="15" style="width: 100%; margin: auto;" ></textarea></div>
<div class="inplayquotes_submit"><input type="submit" value="{$lang->iq_quote_submit}" name="new_quote" id="new_quote"></div>
	</div>
	</form>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'inplayquotes_profile',
        'template' => $db->escape_string('<div class="inplayquotes_profile">
	<div class="inplayquotes_profile_quote">{$quote}</div>
	<div class="inplayquote_profile_outof">{$quote_outof}</div>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    // CSS 
    //CSS einfügen
    $css = array(
        'name' => 'inplayquotes.css',
        'tid' => 1,
        'attachedto' => '',
        "stylesheet" => '.inplayquotes_box{
	margin: 10px 20px;	
}

.inplayquotes_subject{
	text-align: center;
	font-size: 13px;
	margin: 5px auto;
	background: #0066a2 url(../../../images/thead.png) top left repeat-x;
  color: #ffffff;
  border-bottom: 1px solid #263c30;
  padding: 8px;
}

.inplayquotes_from{
	text-align: center;
	font-size: 12px;
	margin: 5px auto;
}

.inplayquotes_quote{
background: #0f0f0f url(../../../images/tcat.png) repeat-x;
  color: #fff;
  border-top: 1px solid #444;
  border-bottom: 1px solid #000;
  padding: 6px;
  font-size: 12px;
		text-align: center;
	box-sizing: border-box;
	margin: 10px auto;
}

.inplayquotes_textarea{
	margin: 10px auto;
	padding: 0 10px;
	width: 500px;
}

.inplayquotes_submit{
	text-align: center;
	margin:  auto;
}

/*inplayquotes misc*/

.inplayquotes_misc_flex{
		display: flex;
		flex-wrap: wrap;
		justify-content: start-flex;
}

.inplayquotes_misc_box{
	width: 47%;
	padding: 10px;
	box-sizing: border-box;
	border-top: 1px solid #fff;
  border-bottom: 1px solid #ccc;
	margin: 10px 20px;
}

.inplayquotes_misc_avatar{
	width: 10%;
	margin: 10px;
}

.inplayquotes_misc_avatar img{
	width: 100%;	
}

.inplayquotes_misc_avatar_box{
		width: 87%;
		margin:10px 5px;
		padding: 10px;
	box-sizing: border-box;
	border-top: 1px solid #fff;
  border-bottom: 1px solid #ccc;
}

.inplayquotes_misc_quote{
	width: 90%;
	text-align: justify;
	margin: 5px 10px;
	height: 75px;
	overflow: auto;
	padding: 2px 5px;
	box-sizing: border-box;
}
.inplayquotes_misc_info{
	font-size: 11px;
	text-align: center;
}

/*index*/

.inplayquotes_index_by{
	font-weight: bold;
	font-size: normal;
		text-align: center;
}

.inplayquotes_index_quote{
		width: 90%;
	text-align: justify;
	margin: 5px auto;
	padding: 2px 5px;
	box-sizing: border-box;
	margin: auto;
}

.inplayquotes_index_outof{
font-weight: bold;
	text-align: center;
}

.inplayquotes_index_goto{
		text-align: center;
}

.inplayquotes_index_noquote{
	font-weight: bold;
	text-align: center;
} 
    

/*profile*/

.inplayquotes_profile{
	margin: 10px 20px;
	text-align: center;
}

.inplayquotes_profile_quote{
	font-size: 14px;
}

.inplayquotes_profile_quote::before{
	content: "»";
	font-size: 14px;
	padding: 1px 5px 1px 0;
}

.inplayquotes_profile_quote::after{
	content: "«";
	font-size: 14px;
	padding: 1px 0 1px 5px;
}

.inplayquote_profile_outof{
	font-size: 10px;
	text-transform: uppercase;
}
',
        'cachefile' => $db->escape_string(str_replace('/', '', 'inplayquotes.css')),
        'lastmodified' => time()
    );

    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

    $sid = $db->insert_query("themestylesheets", $css);
    $db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);

    $tids = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($tids)) {
        update_theme_stylesheet_list($theme['tid']);
    }

    // Don't forget this!
    rebuild_settings();
}

function inplayquotes_is_installed()
{
    global $db;
    if ($db->table_exists("inplayquotes")) {
        return true;
    }
    return false;
}

function inplayquotes_uninstall()
{
    global $db, $cache;

    $db->delete_query('settings', "name IN ('iq_selectforum', 'iq_avatar', 'iq_profilfield', 'iq_get_profilfield', 'iq_withava')");
    $db->delete_query('settinggroups', "name = 'inplayquotes'");

    rebuild_settings();

    if ($db->field_exists("canquoteinplay", "usergroups")) {
        $db->drop_column("usergroups", "canquoteinplay");
    }

    $cache->update_usergroups();

    if ($db->table_exists("inplayquotes")) {
        $db->drop_table("inplayquotes");
    }

    $db->delete_query("templates", "title LIKE '%inplayquotes%'");

    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";
    $db->delete_query("themestylesheets", "name = 'inplayquotes.css'");
    $query = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($query)) {
        update_theme_stylesheet_list($theme['tid']);
    }


    rebuild_settings();
}

function inplayquotes_activate()
{
    global $db, $cache;

    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('iq_alert'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);

    }


    require MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("postbit", "#" . preg_quote('	{$post[\'button_edit\']}') . "#i", '{$post[\'quotes\']}	{$post[\'button_edit\']}');
    find_replace_templatesets("postbit_classic", "#" . preg_quote('	{$post[\'button_edit\']}') . "#i", '{$post[\'quotes\']}	{$post[\'button_edit\']}');
    find_replace_templatesets("index_boardstats", "#" . preg_quote('{$whosonline}') . "#i", '{$whosonline}{$inplayquotes_index}');
    find_replace_templatesets("member_profile", "#" . preg_quote('{$awaybit}') . "#i", '{$inplayquotes_profile}{$awaybit}');
}

function inplayquotes_deactivate()
{
    global $db, $cache;

    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertTypeManager->deleteByCode('iq_alert');
    }
    require MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("postbit", "#" . preg_quote('{$post[\'quotes\']}') . "#i", '', 0);
    find_replace_templatesets("postbit_classic", "#" . preg_quote('{$post[\'quotes\']}') . "#i", '', 0);
    find_replace_templatesets("index_boardstats", "#" . preg_quote('{$inplayquotes_index}') . "#i", '', 0);
    find_replace_templatesets("member_profile", "#" . preg_quote('{$inplayquotes_profile}') . "#i", '', 0);
}
function inplayquotes_permission($above)
{
    global $mybb, $lang, $form;

    if ($above['title'] == $lang->misc && $lang->misc) {
        $above['content'] .= "<div class=\"group_settings_bit\">" . $form->generate_check_box("canquoteinplay", 1, "Kann Inplayquotes hinzufügen?", array("checked" => $_POST['canquoteinplay'])) . "</div>";
    }

    return $above;
}

function inplayquotes_permission_commit()
{
    global $mybb, $updated_group;
    $updated_group['canquoteinplay'] = $mybb->get_input('canquoteinplay', MyBB::INPUT_INT);
}

// ADMIN-CP PEEKER
$plugins->add_hook('admin_config_settings_change', 'inplayquotes_settings_change');
$plugins->add_hook('admin_settings_print_peekers', 'inplayquotes_settings_peek');
function inplayquotes_settings_change()
{
    global $db, $mybb, $inplayquotes_settings_peeker;

    $result = $db->simple_select('settinggroups', 'gid', "name='inplayquotes'", array("limit" => 1));
    $group = $db->fetch_array($result);
    $inplayquotes_settings_peeker = ($mybb->input['gid'] == $group['gid']) && ($mybb->request_method != 'post');
}
function inplayquotes_settings_peek(&$peekers)
{
    global $mybb, $inplayquotes_settings_peeker;

    if ($inplayquotes_settings_peeker) {
        $peekers[] = 'new Peeker($(".setting_iq_withava"), $("#row_setting_iq_avatar"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_iq_withava"), $("#row_setting_iq_profilfield"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_iq_profilfield"), $("#row_setting_iq_get_profilfield"),/1/,true)';
   

    }
}


function inplayquotes_postbit(&$post)
{
    global $mybb, $templates, $db, $lang, $forum, $thread;
    $lang->load('inplayquotes');

    // Settings
    $inplayforum = $mybb->settings['iq_selectforum'];

    if ($inplayforum != -1 or !empty($inplayforum)) {
        $allforum = explode(",", $inplayforum);

        foreach ($allforum as $ipforum) {
            $forum['parentlist'] = "," . $forum['parentlist'] . ",";
            if (preg_match("/,$ipforum,/i", $forum['parentlist'])) {

                $lang->iq_quote_outof = $lang->sprintf($lang->iq_quote_outof, $thread['subject']);
                $lang->iq_quote_from = $lang->sprintf($lang->iq_quote_from, $post['username']);

                eval ("\$post['quotes'] = \"" . $templates->get("inplayquotes_postbit") . "\";");
                return $post;
            }

        }


    }

}


// misc
function inplayquotes_misc()
{

    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $db, $lang, $parser, $subject, $author, $quote, $quote_by, $quote_outof, $option;
    $lang->load('inplayquotes');

    require_once MYBB_ROOT . "inc/class_parser.php";
    $parser = new postParser;

    $options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0
    );

    $own_uid = $mybb->user['uid'];
    $setting_withava = $mybb->settings['iq_withava'];
    $setting_avatar = $mybb->settings['iq_avatar'];
    $setting_pf = $mybb->settings['iq_profilfield'];
    $setting_pf_fid = $mybb->settings['iq_get_profilfield'];


    if ($mybb->get_input('action') == 'add_quote') {
        if (isset($_POST['new_quote'])) {
            // Informationen ziehen
            $uid = $_POST['uid'];
            $tid = $_POST['tid'];
            $pid = $_POST['pid'];
            $quote = $db->escape_string($_POST['inplayquote']);

            $new_quote = array(
                "uid" => $uid,
                "tid" => $tid,
                "pid" => $pid,
                "quote" => $quote
            );


            // Alert auslösen, weil wir wollen ja bescheid wissen, ne?!
            if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('iq_alert');
                if ($alertType != NULL && $alertType->getEnabled() && $uid != 0 && $own_uid != $uid) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int) $uid, $alertType);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }

            $db->insert_query("inplayquotes", $new_quote);
            redirect("showthread.php?tid={$tid}&pid={$pid}#pid{$pid}");
        }
    }


    if ($mybb->get_input('action') == 'inplayquotes') {
        // Do something, for example I'll create a page using the hello_world_template

        // Add a breadcrumb
        add_breadcrumb($lang->iq_misc, "misc.php?action=inplayquotes");

        // Einmal die Zitate auslesen :D

        $all_quotes = $db->query("SELECT *, t.subject
        FROM " . TABLE_PREFIX . "inplayquotes iq
        LEFT JOIN " . TABLE_PREFIX . "users u
        on (iq.uid = u.uid)
        LEFT JOIN " . TABLE_PREFIX . "threads t
        on (t.tid = iq.tid)
        LEFT JOIN " . TABLE_PREFIX . "posts p
        on (p.pid = iq.pid)
        ORDER BY u.username ASC
        ");

        while ($quotes = $db->fetch_array($all_quotes)) {
            $subject = "";
            $quote = "";
            $author = "";
            $tid = 0;
            $pid = 0;
            $avatar = "";
            $option = "";
            $uid = 0;

            $tid = $quotes['tid'];
            $pid = $quotes['pid'];
            $uid = $quotes['uid'];
            $subject = "<a href='showthread.php?tid={$tid}&pid={$pid}#pid{$pid}'>{$quotes['subject']}</a>";
            $quote = $parser->parse_message($quotes['quote'], $options);
            $author = build_profile_link($quotes['username'], $uid);

            $quote_by = $lang->sprintf($lang->iq_misc_quoteby, $author);
            $quote_outof = $lang->sprintf($lang->iq_misc_outof, $subject);



            if ($mybb->usergroup['canmodcp'] == 1 or $mybb->user['uid'] == $uid) {
                eval ('$option  = "' . $templates->get('inplayquotes_misc_option') . '";');
            }

            if ($setting_withava == 0) {
                eval ('$inplayquotes_misc_bit  .= "' . $templates->get('inplayquotes_misc_bit') . '";');
            } else {
                if ($setting_avatar == 1 && $setting_pf == 0) {
                    $avatar = $quotes['avatar'];
                } elseif ($setting_avatar == 0 && $setting_pf == 1) {
                    $avatar = $db->fetch_field($db->simple_select("userfields", "{$setting_pf_fid}", "ufid = '{$uid}'"), $setting_pf_fid);
                }
                eval ('$inplayquotes_misc_bit  .= "' . $templates->get('inplayquotes_misc_bit_avatar') . '";');
            }
        }

        $deletequote = $mybb->input['deletequote'];

        if ($deletequote) {
            $db->delete_query("inplayquotes", "qid = {$deletequote}");
            redirect("misc.php?action=inplayquotes");
        }

        // Using the misc_help template for the page wrapper
        eval ("\$page = \"" . $templates->get("inplayquotes_misc") . "\";");
        output_page($page);
    }
}



function inplayquotes_index()
{
    global $db, $mybb, $templates, $lang, $parser, $inplayquotes_index, $inplayquotes_index_bit, $inplayquotes_forumbit_bit, $inplayquotes_forumbit;
    $lang->load('inplayquotes');

    require_once MYBB_ROOT . "inc/class_parser.php";
    $parser = new postParser;

    $options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0
    );

    $all_quotes = $db->query("SELECT *, t.subject
        FROM " . TABLE_PREFIX . "inplayquotes iq
        LEFT JOIN " . TABLE_PREFIX . "users u
        on (iq.uid = u.uid)
        LEFT JOIN " . TABLE_PREFIX . "threads t
        on (t.tid = iq.tid)
        LEFT JOIN " . TABLE_PREFIX . "posts p
        on (p.pid = iq.pid)
    ORDER BY rand()
    LIMIT 1
    ");

    $quotes = $db->fetch_array($all_quotes);
    $subject = "";
    $quote = "";
    $author = "";
    $tid = 0;
    $pid = 0;
    $avatar = "";
    $option = "";

    $tid = $quotes['tid'];
    $pid = $quotes['pid'];

    $subject = "<a href='showthread.php?tid={$tid}&pid={$pid}#pid{$pid}'>{$quotes['subject']}</a>";
    $quote = $parser->parse_message($quotes['quote'], $options);
    $author = build_profile_link($quotes['username'], $quotes['uid']);


    $quote_by = $lang->sprintf($lang->iq_index_quoteby, $author);
    $quote_outof = $lang->sprintf($lang->iq_index_outof, $subject);
    if (!empty($quotes)) {
        eval ('$inplayquotes_index_bit  = "' . $templates->get('inplayquotes_index_bit') . '";');
        eval ('$inplayquotes_forumbit_bit  = "' . $templates->get('inplayquotes_forumbit_bit') . '";');

    } else {
        $inplayquotes_index_bit = $lang->iq_index_noquotes;
        $inplayquotes_forumbit_bit = $lang->iq_index_noquotes;
    }
    eval ('$inplayquotes_index  = "' . $templates->get('inplayquotes_index') . '";');
    eval ('$inplayquotes_forumbit  = "' . $templates->get('inplayquotes_forumbit') . '";');
}


function inplayquotes_member_profile()
{
    global $db, $mybb, $templates, $lang, $parser, $memprofile, $quote, $quote_outof, $inplayquotes_profile;
    $lang->load('inplayquotes');

    require_once MYBB_ROOT . "inc/class_parser.php";
    $parser = new postParser;

    $options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0
    );



    $uid = $memprofile['uid'];

    $all_quotes = $db->query("SELECT *, t.subject
    FROM " . TABLE_PREFIX . "inplayquotes iq
    LEFT JOIN " . TABLE_PREFIX . "users u
    on (iq.uid = u.uid)
    LEFT JOIN " . TABLE_PREFIX . "threads t
    on (t.tid = iq.tid)
    LEFT JOIN " . TABLE_PREFIX . "posts p
    on (p.pid = iq.pid)
    where iq.uid = '" . $uid . "'
    ORDER BY rand()
    LIMIT 1
    ");

    $quotes = $db->fetch_array($all_quotes);
    $subject = "";
    $quote = "";
    $author = "";
    $tid = 0;
    $pid = 0;
    $avatar = "";
    $option = "";

    $tid = $quotes['tid'];
    $pid = $quotes['pid'];

    $subject = "<a href='showthread.php?tid={$tid}&pid={$pid}#pid{$pid}'>{$quotes['subject']}</a>";
    $quote = $parser->parse_message($quotes['quote'], $options);
    $quote_outof = $lang->sprintf($lang->iq_profile_outof, $subject);

    if (!empty($quotes)) {
        eval ('$inplayquotes_profile  = "' . $templates->get('inplayquotes_profile') . '";');
    }

}


// Inplayquotes von gelöschten Accounts löschen.
function inplayquotes_global()
{

    delete_old_quotes();

}


function delete_old_quotes()
{
    global $db, $mybb;

    $get_oldquotes = $db->query("SELECT uid
FROM " . TABLE_PREFIX . "inplayquotes
where uid not in (SELECT uid
      FROM " . TABLE_PREFIX . "users)
");
    while ($deltequotes = $db->fetch_array($get_oldquotes)) {
        $db->delete_query("applications", "uid = {$deltequotes['uid']}");
    }


}

function inplayquotes_alerts()
{
    global $mybb, $lang;
    $lang->load('inplayquotes');

    /**
     * Alert, wenn der Steckbrief zur Korrektur übernommen worden ist.
     */
    class MybbStuff_MyAlerts_Formatter_InplayquotesFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->iq_alert,
                $outputAlert['from_user'],
                $outputAlert['dateline']
            );
        }


        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {
        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/misc.php?action=inplayquotes';
        }
    }


    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_InplayquotesFormatter($mybb, $lang, 'iq_alert')
        );
    }
}
