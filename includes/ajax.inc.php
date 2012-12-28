<?php
require dirname(__FILE__).'/../functions.inc.php';
$dahdi_cards = new dahdi_cards();


switch($_REQUEST['type']) {
    case "modprobe":
        $sql = "SELECT settings FROM dahdi_advanced_modules WHERE module_name = '".mysql_real_escape_string($_REQUEST['dcmodule'])."'";
        $settings = sql($sql, 'getOne');
        if($settings) {
            $json = array(
                "status" => true, 
                "module" => $_REQUEST['dcmodule']
                );                    
            
            $settings = json_decode($settings,TRUE);
            $json = array_merge($settings,$json);
        } else {
            if($_REQUEST['dcmodule'] == 'wctc4xxp') {
                $json = array(
                    "status" => true, 
                    "module" => $_REQUEST['dcmodule'],
                    "mode_checkbox" => false,
                    "mode" => "any"
                );
            } else {
                $json = array(
                    "status" => true, 
                    "module" => $_REQUEST['dcmodule'],
            		'opermode_checkbox'=>FALSE,
            		'opermode'=>'USA',
            		'alawoverride_checkbox'=>FALSE,
            		'alawoverride'=>0,
            		'fxs_honor_mode_checkbox'=>FALSE,
            		'fxs_honor_mode'=>0,
            		'boostringer_checkbox'=>FALSE,
            		'boostringer'=>0,
            		'fastringer_checkbox'=>FALSE,
            		'fastringer'=>0,
            		'lowpower_checkbox'=>FALSE,
            		'lowpower'=>0,
            		'ringdetect_checkbox'=>FALSE,
            		'ringdetect'=>0,
            		'mwi_checkbox'=>FALSE,
            		'mwi'=>'none',
            		'neon_voltage'=>'',
            		'neon_offlimit'=>'',
            		'echocan_nlp_type'=>0,
            		'echocan_nlp_threshold'=>'',
            		'echocan_nlp_max_supp'=>'' 
            		);
    		}
        }
        break;
    case "modprobesubmit":
        $modprobe = array();
        
        dbug($_REQUEST);
        if(isset($_REQUEST['settings'])) {
            $settings = json_decode($_REQUEST['settings'], TRUE);
            foreach ($dahdi_cards->get_all_modprobe($settings['module_name']) as $k=>$v) {
        		$modprobe[$k] = $settings[$k];
            }
            $dahdi_cards->update_dahdi_modprobe($modprobe);
        }
        /*
    	foreach ($dahdi_cards->get_all_modprobe($_REQUEST['module_name']) as $k=>$v) {	    
    		if ( ! isset($_REQUEST[$k])) {	
    			if (strpos($k, 'checkbox')) {
    				$modprobe[$k] = FALSE;
    			} else {
    				$modprobe[$k] = TRUE;
    			}
    			continue;
    		}  
    		$modprobe[$k] = $_POST[$k];  	        
    	}
    	$dahdi_cards->update_dahdi_modprobe($modprobe);
    	*/	
    	needreload();
    	
	    $json = array("status" => true);
        break;
    case "globalsettingssubmit":
        foreach ($dahdi_cards->get_all_globalsettings() as $k=>$v) {
    	    if ( ! isset($_POST[$k])) {
    			if (strpos($k, 'checkbox')) {
    				$gs[$k] = FALSE;
    			} else {
    				$gs[$k] = TRUE;
    			}
    			continue;
    		}
    		$gs[$k] = $_POST[$k]; 
    	}
    	
        $i = 0;
        while(TRUE) {
            if(!empty($_POST['dh_global_setting_key_'.$i])) {
                $k = $_POST['dh_global_setting_key_'.$i];
                $gs[$k] = isset($_POST['dh_global_setting_value_'.$i]) ? $_POST['dh_global_setting_value_'.$i] : '';
                if(isset($_POST['dh_global_setting_checkbox_'.$i])) {
                    $gs[$k.'_checkbox'] = TRUE;
                } else {
                    $gs[$k.'_checkbox'] = FALSE;
                }
            } else {
                break;
            }
            $i++;
        }
    	$dahdi_cards->update_dahdi_globalsettings($gs);
    	needreload();
        $json = array("status" => true);
        break;
    case "digital":
        $editspan = array();
        
        

	    $vars = array('fac', 'channels', 'signalling', 'switchtype', 'syncsrc', 'lbo', 'pridialplan', 'prilocaldialplan', 'reserved_ch', 'priexclusive');
	    $id = isset($_GET['id']) ? $_GET['id'] : '';
	    foreach ($vars as $var) {
		    $editspan[$var] = $_POST['editspan_'.$id.'_'.$var];
	    }
	    $editspan['span'] = $id;
	    
	    $editspan['groupdata'] = json_decode($_REQUEST['groupdata'],TRUE);
	    
	    foreach($editspan['groupdata'] as $key=> $gd) {
	        if(!empty($_REQUEST['editspan_'.$id.'_context_'.$key])) {
	            $editspan['groupdata'][$key]['group'] = $_REQUEST['editspan_'.$id.'_group_'.$key];
	            $editspan['groupdata'][$key]['context'] = $_REQUEST['editspan_'.$id.'_context_'.$key];
            } else {
                unset($editspan['groupdata'][$key]);
            }
	    }
	    
	    $editspan['additional_groups'] = json_encode($editspan['groupdata']);
	    $dahdi_cards->update_span($editspan);
	    	    
	    $json = $dahdi_cards->get_span($id);
	    $json['totchans'] = $json['totchans']."/".$json['totchans'];
	    $json['framingcoding'] = $json['framing']."/".$json['coding'];

	    $json['status'] = TRUE;
        
        needreload();
        break;
    case "analog":
        $type = $_GET['ports'];

    	$spans = ($type == 'fxo') ? $dahdi_cards->get_fxo_ports() : $dahdi_cards->get_fxs_ports();
    	foreach ($spans as $span) {
    		$port = array();
    		$port['signalling'] = $_POST[$type."_port_{$span}"];
    		$port['group'] = ($_POST[$type."_port_{$span}_group"])?$_POST[$type."_port_{$span}_group"]:0;
    		$port['context'] = $_POST[$type."_port_{$span}_context"];
    		$dahdi_cards->set_analog_signalling($span, $port);
    		unset($port);
    	}


    	$dahdi_cards->write_analog_signalling();
    	$json = array("status" => true);
        break;
    case "calcbchanfxx":
        $o = $dahdi_cards->calc_bchan_fxx($_REQUEST['span'],NULL,$_REQUEST['startchan'],$_REQUEST['usedchans']);
        $json = array(
            "fxx" => $o['fxx'],
            "endchan" => $o['endchan'],
            "startchan" => $o['startchan'],
            "status" => true
            );
        break;
    case "spandata":
        $span = $dahdi_cards->get_span($_REQUEST['span']);
        $json = array(
            "span" => $span,
            "status" => true
            );
        break;
    case "digitaladd":
        $groupc = $_REQUEST['groupc'];
        $context = 'from-digital';
        $opts = '';
        
        $span = $dahdi_cards->get_span($_REQUEST['span']);
        $c = (int)$_REQUEST['usedchans'];
        $s = (int)$_REQUEST['startchan'];
        //$opts .= '<option value="0">0</option>';
        for($i=1; $i<=$c; $i++) {
            $selected = ($i == $c) ? 'selected' : '';
    	    $opts .= '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
        }
        
        $o = $dahdi_cards->calc_bchan_fxx($_REQUEST['span'],NULL,$s,$c);
        $html = <<<EOF
        <table width="100%" id="editspan_{$span['id']}_group_settings_${groupc}" style="text-align:left;" border="0" cellspacing="0">
            <tr>
                <td style="width:10px;">
                    <label>Group: </label>
                </td>
                <td>
            	    <input type="text" id="editspan_{$span['id']}_group_${groupc}" name="editspan_{$span['id']}_group_${groupc}" size="2" value="{$groupc}" />            	    
                </td>
            </tr>
            <tr>
                <td style="width:10px;">
                    <label>Context: </label>
                </td>
                <td>
            	    <input type="text" id="editspan_{$span['id']}_context_${groupc}" name="editspan_{$span['id']}_context_${groupc}" value="$context" />            	    
                </td>
            </tr>
            <tr>
                <td style="width:10px;">
                    <label>Used Channels: </label>
                </td>
                <td>
                    <select id="editspan_{$span['id']}_definedchans_${groupc}" name="editspan_{$span['id']}_definedchans_${groupc}">
                        $opts
                	</select>
                	From: <span id="editspan_{$span['id']}_from_${groupc}">{$o['fxx']}</span>
                	Reserved: <span id="editspan_{$span['id']}_reserved_${groupc}">{$span['reserved_ch']}</span>
                </td>
            </tr>
        </table>
EOF;
        $json = array("html" => $html, "status" => true, "select" => $opts, "fxx" => $o['fxx'], "endchan" => $o['endchan'], "chansleft" => $c, "startchan" => $o['startchan'], "span" => $span,);
        break;
    default:
        $json = array("status" => false);
        break;
}

echo json_encode($json);