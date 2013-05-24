<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

/**
 * FreePBX DAHDi Config Module
 *
 * Copyright (c) 2009, Digium, Inc.
 * Copyright (c) 2012, Schmooze Com Inc
 * Author: Ryan Brindley <ryan@digium.com>
 * Author: Schmooze Com Inc
 *
 * This program is free software, distributed under the terms of
 * the GNU General Public License Version 2. 
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
 
 /*
 function GetCallingMethodName(){
     $e = new Exception();
     $trace = $e->getTrace();
     //position 0 would be the line that called this function so we ignore it
     $last_call = $trace[1];
     return($last_call);
 }
 */
 
require_once('includes/dahdi_cards.class.php');


global $db;

/**
 * DAHDI CONF
 * 
 * This class contains all the functions to configure asterisk via freepbx
 */
 class dahdiconfig_conf {
	var $cards;

 	public function dahdiconfig_conf() {
		$this->cards = new dahdi_cards();

        $this->cards->dahdi_cfg();
        global $amp_conf;
        if (!$amp_conf['DAHDIDISABLEWRITE']) {
    		$this->cards->write_modprobe();
    		$this->cards->write_system_conf();
            $this->cards->write_modules();
        }
	}

	public function get_filename() {
        global $amp_conf;
		return !$amp_conf['DAHDIDISABLEWRITE'] ? array('chan_dahdi_general.conf', 'chan_dahdi_groups.conf', 'chan_dahdi.conf') : array();
	}

	public function generateConf($file) {
		switch($file) {
		    case 'chan_dahdi.conf':
		        if(!file_exists('/etc/asterisk/chan_dahdi.conf.old')) {
		            //Quick Backup http://www.freepbx.org/trac/ticket/4558
		            rename('/etc/asterisk/chan_dahdi.conf','/etc/asterisk/chan_dahdi.conf.old');
		        }
		        $output = array();
		        $output[] = "[general]";
		        $output[] = "";
		        $output[] = "; generated by module";
		        $output[] = "#include chan_dahdi_general.conf";
		        $output[] = "";
		        $output[] = "; for user additions not provided by module";
		        $output[] = "#include chan_dahdi_general_custom.conf";
		        $output[] = "";
		        $output[] = "[channels]";
		        
		        foreach($this->cards->get_all_globalsettings() as $k => $v) {
                        $output[] = $k."=".$v;
                }
                
		        $output[] = "";
		        $output[] = "; for user additions not provided by module";
		        $output[] = "#include chan_dahdi_channels_custom.conf";
		        $output[] = "";
		        $output[] = "; include dahdi groups defined by DAHDI module of FreePBX";
		        $output[] = "#include chan_dahdi_groups.conf";
		        $output[] = "";
		        $output[] = "; include dahdi extensions defined in FreePBX";
		        $output[] = "#include chan_dahdi_additional.conf";
		        $output[] = "";
		        return implode("\n", $output);
    		case 'chan_dahdi_general.conf':
    			$output = array();
		
		        /* TODO: Andrew Fix
    			if ( ! $this->cards->get_advanced('mwi_checkbox')) {
    				return '';	
    			}

    			if ($this->cards->get_advanced('mwi') == 'fsk') {
    				$output[] = "mwimonitor=fsk";
    				$output[] = "mwilevel=512";
    				$output[] = "mwimonitornotify=__builtin__";
    			} else if ($this->cards->get_advanced('mwi') == 'neon') {
    				$output[] = "mwimonitor=neon";
    				$output[] = "mwimonitornotify=__builtin__";
    			}
    			*/

    			return implode("\n", $output);
    		case 'chan_dahdi_groups.conf':
    			$output = array();

    			foreach ($this->cards->get_spans() as $key=>$span) {
    				if (!isset($span['signalling']) || $span['signalling'] == '') {
    					continue;
    				}

    				$output[] = "";
    				$output[] = "; [span_{$key}]";
					if($span['devicetype'] == 'W400') {
						$output[] = "echocancel=no";
						$output[] = "echocancelwhenbridged=no";
						$output[] = "signalling=gsm";
						$output[] = "wat_moduletype=telit";
					} else {
	    				$output[] = "signalling={$span['signalling']}";
	    				$output[] = "switchtype={$span['switchtype']}";
	    				$output[] = "pridialplan={$span['pridialplan']}";
	    				$output[] = "prilocaldialplan={$span['prilocaldialplan']}";
						if(!empty($span['txgain']) && $span['txgain'] != '0.0')
							$output[] = "txgain={$span['txgain']}";
						if(!empty($span['rxgain']) && $span['rxgain'] != '0.0')
							$output[] = "rxgain={$span['rxgain']}";
					}
    				
    				$groups = json_decode($span['additional_groups'],TRUE); 
                    foreach($groups as $gkey => $data) {
    				    $output[] = "group={$data['group']}";
    				    $output[] = "context={$data['context']}";
    				    $output[] = "channel={$data['fxx']}";
				    }
    				
    				$output[] = !empty($span['priexclusive']) ? "priexclusive={$span['priexclusive']}" : "";
    			}

    			foreach ($this->cards->get_analog_ports() as $num=>$port) {
    				if ($port['type'] == '') {
    					continue;
    				}

    				$output[] = "";
    				$output[] = "signalling=".(($port['type']=='fxo')?'fxs':'fxo')."_{$port['signalling']}";
    				$output[] = "context={$port['context']}";
					if(!empty($port['txgain']) && $port['txgain'] != '0.0')
						$output[] = "txgain={$port['txgain']}";
					if(!empty($port['rxgain']) && $port['rxgain'] != '0.0')
						$output[] = "rxgain={$port['rxgain']}";
    				$output[] = isset($port['group']) ? "group={$port['group']}" : "group=0";
    				$output[] = "channel=>{$num}";
    			}

    			return implode("\n", $output);
    		default:
    			return '';
		}
	}

 }

function dahdi_config2array ($config) {
	if (! is_array($config)) {
		$config = explode("\n", $config);	
	}

	$cxts = array();
	$cxt = '';

	unset($config[count($config)-1]);
	
	for($i=0;$i<count($config);$i++) {
		unset($matches);
		if ($config[$i] == '') {
			continue;
		} else if (preg_match('/^\[([-a-zA-Z0-9_][-a-zA-Z0-9_]*)\]/', $config[$i], $matches)) {
			$cxt = $matches[1];
			$cxts[$cxt] = array();
			continue;
		}

		if ($cxt == '') {
			continue;
		}

		list($var, $val) = explode('=',$config[$i]);
		
		if (isset($cxts[$cxt][$var])) {
			if (gettype($cxts[$cxt][$var]) !== 'array') {
				$cxts[$cxt][$var] = array($cxts[$cxt][$var]);
			}

			$cxts[$cxt][$var][] = $val;
		} else {
			$cxts[$cxt][$var] = $val;
		}
	}

	return $cxts;
}

function dahdi_chans2array($chans=null) {
	if (!$chans || $chans = '') {
		return array();
	}

	$chanarray = array();
	
	if (strpos($chans,',') && strpos($chans,'-')) {
		$segs = explode(',',$chans);
		foreach ($segs as $seg) {
			if (strpos($chans,'-')) {
				list($start, $end) = explode('-',$chans);
				for($i=$start;$i<=$end;$i++) {
					$chanarray[] = $i;
				}
				continue;
			}

			$chanarray[] = $seg;
		}
	} else if (strpos($chans,',')) {
		$chanarray = explode(',',$chans);	
	} else if (strpos($chans,'-')) {
		list($start,$end) = explode('-',$chans);
		for($i=$start; $i<=$end; $i++) {
			$chanarray[] = $i;
		}
	} else {
		$chanarray = array($chans);
	}

	return $chanarray;
}

function dahdi_array2chans($arr) {
	$conf_chans = array();
	$total_chans = count($arr)-1;
	$seq = 0;
	$seq_count = 0;
	$first = '';
	foreach($arr as $key => $chan) {
	    //Separator 
	    $sep = ($seq_count > 0) ? '-' : ',';
	    switch($key) {
	        case 0:
	            //First chan in array
	            $first = $chan;
	            $prev = $chan;
	            $conf_chans[$seq] = $chan;
	            break;
	        case $total_chans:
	            //Last chan in array
	            if($prev == ($chan-1)) {
	                $conf_chans[$seq] = $first.$sep.$chan;
                } else {
                    $seq++;
                    $conf_chans[$seq] = $chan;
                }
	            break;
	        default:
	            if($prev == ($chan-1)) {
	                //Old Set
	                $conf_chans[$seq] = $first.$sep.$chan;
	                $seq_count++;
	                $prev = $chan;
	            } else {
	                //New Set
	                $seq++;
	                $conf_chans[$seq] = $chan;
	                $seq_count = 0;
	                $first = $chan;
	                $prev = $chan; 
	            }
	            break;
	    }
	}
	return implode(',',$conf_chans);
}

// list unused DAHDI fxs channels that can be configured for extensions
//
function dahdiconfig_get_unused_fxs_channels($current_device='') {
  $all_channels = sql('SELECT * FROM dahdi_analog WHERE type = "fxs"','getAll',DB_FETCHMODE_ASSOC); 
  $used_channels = sql('SELECT id device, data port FROM dahdi WHERE keyword = "channel"','getAll',DB_FETCHMODE_ASSOC); 
  $used_channels_hash = array();
  foreach ($used_channels as $chan) {
    $used_channels_hash[$chan['port']] = $chan['device'];
  }

  $avail_channels = array();
  foreach ($all_channels as $chan) {
    if (isset($used_channels_hash[$chan['port']])) {
      if ($current_device == $used_channels_hash[$chan['port']]) {
        $selected = true;
      } else {
        continue;
      }
    } else {
      $selected = false;
    }
    $avail_channels[] = array('channel' => $chan['port'], 'signalling' => 'fxo_'.$chan['signalling'], 'selected' => $selected);
  }
  return $avail_channels;
}

function _dahdiconfig_gsort($a, $b) {
  $gn_a = substr($a,1);
  $gn_b = substr($b,1);
  if ($gn_a == $gn_b) {
    return ($b > $a);
  } else {
    return ($gn_a > $gn_b);
  }
}

function dahdiconfig_get_unused_trunk_options($current_identifier='') {
  global $amp_conf;
  $avail_group = array();
  $analog_chan = array();
  $digital_chan = array();

  $dahdi_cards = new dahdi_cards();
  $analog_ports = $dahdi_cards->get_fxo_ports();

  // Get Analog Groups and Channels for FXO
  //
  foreach ($analog_ports as $port) {
    $port_details = $dahdi_cards->get_port($port);
    $grp = $port_details['group'];
    $chan = (string) $port_details['port'];
    $avail_group["g$grp"] = array('identifier' => "g$grp",'name' => "Group $grp Ascending",'alarms' => '','selected'  => ($current_identifier == "g$grp"));
    $avail_group["G$grp"] = array('identifier' => "G$grp",'name' => "Group $grp Descending",'alarms' => '','selected' => ($current_identifier == "G$grp"));
    $analog_chan[$chan] = array('identifier' => $chan, 'name' => "Analog Channel $chan",'alarms' => '','selected' => ($current_identifier == $chan));
  }
  // Get Digital Groups and Channels. Channels are not that useful
  // but can be helpful when testing bad channels
  //
  $digital_spans = $dahdi_cards->get_spans();
  foreach ($digital_spans as $span) {
    if (!$span['active']) {
      continue;
    }
    $alarms = $span['alarms'];
    foreach(json_decode($span['additional_groups'],TRUE) as $groups) {
        $grp = $groups['group'];
        if (!isset($avail_group["g$grp"])) {
          $avail_group["g$grp"] = array('identifier' => "g$grp",'name' => "Group $grp Ascending",'alarms' => $alarms,'selected'  => ($current_identifier == "g$grp"));
          $avail_group["G$grp"] = array('identifier' => "G$grp",'name' => "Group $grp Descending",'alarms' => $alarms,'selected' => ($current_identifier == "G$grp"));
        } else {
          //TODO: figure out the possible alarms and the create proper hiearchy of what to report
          //
          if ($alarms == 'RED' || $avail_group["g$grp"]['alarms'] == '') {
            $avail_group["g$grp"]['alarms'] = $alarms;
            $avail_group["G$grp"]['alarms'] = $alarms;
          }
        }
    }
    if ($amp_conf['DAHDISHOWDIGITALCHANS']) {
      $basechan = $span['basechan'];
      $definedchans = $span['definedchans'];
      $topchan = $basechan + $definedchans;
      for ($port = $basechan; $port < $topchan; $port++) {
          if($port != $span['reserved_ch'])
              $digital_chan["$port"] = array('identifier' => "$port", 'name' => "Digital Channel $port",'alarms' => $alarms,'selected' => ($current_identifier == "$port"));
      }
    }
  }
  uksort($avail_group,'_dahdiconfig_gsort');
  ksort($analog_chan);
  if ($amp_conf['DAHDISHOWDIGITALCHANS']) {
    ksort($digital_chan);
    $avail_identifiers = $avail_group + $analog_chan + $digital_chan;
  } else {
    $avail_identifiers = $avail_group + $analog_chan;
  }
  $trunk_list = core_trunks_listbyid();
  foreach ($trunk_list as $trunk) {
    if ($trunk['tech'] != 'dahdi' || $trunk['channelid'] == $current_identifier) {
      continue;
    }
    unset($avail_identifiers[$trunk['channelid']]);
  }
  return ($avail_identifiers);
}

function dahdiconfig_configpageinit($dispnum) {
global $currentcomponent;
switch ($dispnum) {
  case 'devices':
  case 'extensions':
    // if tech_hardware set, this is an initial extension/device creation
    // otherwise, determine if the target extension/device is DAHDI
    //
    if (isset($_REQUEST['tech_hardware']) && $_REQUEST['tech_hardware'] == 'dahdi_generic') {
      $extdisplay = '';
    } else {
      if (!isset($_REQUEST['extdisplay']) || $_REQUEST['extdisplay'] == '') {
        return true;
      }
      $extdisplay = $_REQUEST['extdisplay'];
      $device_info = core_devices_get($extdisplay);
      if (empty($device_info) || $device_info['tech'] != 'dahdi') {
        return true;
      }
    }
  
	  $channel_select  = dahdiconfig_get_unused_fxs_channels($extdisplay);
    $currentcomponent->addoptlistitem('dahdi_channel_select', '', _('==Choose=='));
	  foreach ($channel_select as $val) {
		  $currentcomponent->addoptlistitem('dahdi_channel_select', $val['channel'].':'.$val['signalling'], $val['channel']);
	  }
	  $currentcomponent->setoptlistopts('dahdi_channel_select', 'sort', false);
  break;
  case 'trunks':
    if (isset($_REQUEST['tech']) && strtolower($_REQUEST['tech']) == 'dahdi') {
      $extdisplay = '';
      $_REQUEST['dahdi_current_channel'] = '';
    } else {
      if (!isset($_REQUEST['extdisplay']) || $_REQUEST['extdisplay'] == '') {
        return true;
      }
      $extdisplay = $_REQUEST['extdisplay'];
      $trunknum = ltrim($extdisplay,'OUT_');
      $trunk_details = core_trunks_getDetails($trunknum);
      $tech = $trunk_details['tech'];
      if ($tech != 'dahdi') {
        return true;
      }
      $_REQUEST['dahdi_current_channel'] = $trunk_details['channelid'];
    }
    // dahdiconfig_hook_core will see this and create the needed selelect structure
    //
    $_REQUEST['display_dahdi_select'] = 'true';
  break;
  default;
    return true;
  break;
  }
  $currentcomponent->addguifunc("dahdiconfig_{$dispnum}_configpageload");
}

function dahdiconfig_hook_core($viewing_itemid, $target_menuid) {
  global $tabindex;
  $html = '';
  if ($target_menuid == 'trunks' && isset($_REQUEST['display_dahdi_select']) && $_REQUEST['display_dahdi_select'] == 'true') {

    // TODO: If list is exhausted, write message that no options left
    //
    $avail_trunks = dahdiconfig_get_unused_trunk_options($_REQUEST['dahdi_current_channel']);
    if (!empty($avail_trunks)) {
      $html = '
				<tr>
					<td>
						<a href=# class="info">' . _("DAHDI Trunks") . '<span>' . _("Available DAHDI Groups and Channels configued in the DAHDI Configuration Module") . '</span></a>: 
          </td>
          <td>
            <select name="dahdi_trunks" id="dahdi_trunks" tabindex="' . ++$tabindex . '">
      ';
      foreach ($avail_trunks as $ident) {
        $selected = $ident['selected'] ? ' SELECTED' : '';
        $html .= "
            <option value='{$ident['identifier']}'$selected>{$ident['name']}
        ";
      }
      $html .= "
            </select>
					</td>
        </tr>
      ";
    } else {
      $html .= '
				<tr>
					<td colspan="2">
						<a href=# class="info">' . _("No Available Groups or Channels") . '<span>' . _("There are no DAHDI Groups or Channels available to be configured. Check the DAHDI module (linked below) to configure any un-used cards") . '</span></a> 
          </td>
				</tr>
      ';
    }

	  $URL = $_SERVER['PHP_SELF'].'?'.'display=dahdi';
    $html .= '
				<tr><td colspan="2"><input type="hidden" id="dahdi_trunks" value=""></td></tr>
				<tr>
					<td colspan="2">
						<a href="'.$URL.'" class="info">' . _("Configure/Edit DAHDI Cards") . '<span>' . _("Configure/Edit DAHDI Card settings in DAHDi Module") . '</span></a> 
          </td>
				</tr>
    ';
  }
  return $html;
}

//hook gui function
//
function dahdiconfig_devices_configpageload() {
  dahdiconfig_configpageload('device');
}
function dahdiconfig_extensions_configpageload() {
  dahdiconfig_configpageload('extension');
}
function dahdiconfig_configpageload($mode) {
  global $currentcomponent;

	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;

  $dahdi_channel_select = $currentcomponent->getoptlist('dahdi_channel_select');
  if (!empty($dahdi_channel_select)) {
    // Generate Channel Select, on submit populuate device channel, dial and signalling fields
    $currentcomponent->addguielem('Device Options', new gui_selectbox(
      'dahdi_channel', 
      $dahdi_channel_select,
      '',
      _('Channel'), 
      sprintf(_('Choose the FXS channel for this %s'),$mode),
    false, 
      "javascript:if (document.frm_{$mode}s.dahdi_channel.value) {parts = document.frm_{$mode}s.dahdi_channel.value.split(':');document.frm_{$mode}s.devinfo_channel.value = parts[0];document.frm_{$mode}s.devinfo_dial.value = 'DAHDI/'+parts[0];document.frm_{$mode}s.devinfo_signalling.value = parts[1]; } else { document.frm_{$mode}s.devinfo_channel.value = ''}"
    ));

    // On pageload hide channel, signalling and dial fields and select dahdi_channel based on channel field's contents
    $js = '<script type="text/javascript">
      $(document).ready(function(){
        $("#dahdi_channel").val($("#devinfo_channel").val()+":"+$("#devinfo_signalling").val());
        if ($("#dahdi_channel").val() == null) {
          $("#dahdi_channel").val("");
        }
        $("#devinfo_channel").parent().parent().hide();
        $("#devinfo_signalling").parent().parent().hide();
        $("#devinfo_dial").parent().parent().hide();
      });
    </script>';
    $currentcomponent->addguielem('Device Options', new guielement('dahdi-chan-html', $js, ''));
  } else {
    // No available channels so display that and hide channel, signalling and dial fields
	  $currentcomponent->addguielem('Device Options', new gui_label('no_dahdi_channel', _('No Unused DAHDi Channels Available')));
    $js = '<script type="text/javascript">
      $(document).ready(function(){
        $("#devinfo_channel").parent().parent().hide();
        $("#devinfo_signalling").parent().parent().hide();
        $("#devinfo_dial").parent().parent().hide();
      });
    </script>';
    $currentcomponent->addguielem('Device Options', new guielement('dahdi-chan-html', $js, ''));
  }
}

function dahdiconfig_trunks_configpageload() {
  global $currentcomponent;
	$extdisplay = isset($_REQUEST['extdisplay'])?$_REQUEST['extdisplay']:null;

  $js = '
  <script type="text/javascript">
    $(document).ready(function(){
      $("input[name=\'channelid\']").attr("id","channelid").val($("#dahdi_trunks").val()).parent().parent().hide();
      $("#dahdi_trunks").change(function(){
        $("#channelid").val(this.value);
      });
    });
  </script>';
  //Hide Dahdi Identifier original setting
  $currentcomponent->addguielem('_top', new guielement('dahdi-chan-html', $js, ''));
}

function dahdiconfig_getinfo($info=null) {
	global $astman;
	
	if($astman && $astman->connected()) {
		$o = $astman->send_request('Command', array('Command' => 'dahdi show version'));

		switch ($info) {
			case "version":
				if(preg_match('/DAHDI Version:(.*)Echo Canceller:/i',$o['data'],$matches)) {
					$dahdi_version = trim($matches[1]);
				} else {
					$dahdi_version = 9999;
				}
				return $dahdi_version;
				break;
			default:
				$dahdi_info = explode("\n",$o['data']);
				return $dahdi_info;
				break;
		}
	} else {
		return false;
	}

}