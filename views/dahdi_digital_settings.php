<form id="dahdi_editspan_<?php echo $key?>" method="POST" action="config.php?quietmode=1&amp;handler=file&amp;module=dahdiconfig&amp;file=ajax.html.php&amp;type=digital&amp;id=<?php echo $key;?>">
    <input type="hidden" name="editspan_<?php echo $key?>_reserved_ch" value="<?php echo $span['reserved_ch'];?>">
    <input type="hidden" name="editspan_<?php echo $key?>_groupc" value="0">
    <h2>General Settings</h2>
    <hr>
    <table width="100%" style="text-align:left;" border="0" cellspacing="0">
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_alarm">Alarms:</label>
            </td>
            <td>
                <span id="editspan_<?php echo $key?>_alarms" name="editspan_<?php echo $key?>_alarms"><?php echo $span['alarms']?></span>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_framing">Framing/Coding:</label>
            </td>
            <td>
               	<select id="editspan_<?php echo $key?>_fac" name="editspan_<?php echo $key?>_fac">
            	<?php switch($span['totchans']) {
            	   case 3: ?>
            		<option value="CCS/AMI" <?php echo set_default($span['framing']."/".$span['coding'],'CCS/AMI'); ?>></option>
            	<?php 	break;
            	   case 24: ?>
            		<option value="ESF/B8ZS" <?php echo set_default($span['framing']."/".$span['coding'],'ESF/B8ZS'); ?>>ESF/B8ZS</option>
            		<option value="D4/AMI" <?php echo set_default($span['framing']."/".$span['coding'],'D4/AMI'); ?>>D4/AMI</option>
            	<?php 	break;
            	   case 31: ?>
            		<option value="CCS/HDB3" <?php echo set_default($span['framing']."/".$span['coding'], 'CCS/HDB3'); ?>>CCS/HDB3</option>
            		<option value="CCS/HDB3/CRC4" <?php echo set_default($span['framing']."/".$span['coding'],'CCS/HDB3/CRC4'); ?>>CCS/HDB3/CRC4</option>
            	   <?php	break;
            	   default:
            	   	break;
            	} ?>
            	</select> 
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_channels">Channels:</label>
            </td>
            <td>
                <span id="editspan_<?php echo $key?>_channels"><?php echo "{$span['definedchans']}/{$span['totchans']} ({$span['spantype']})"?></span>
            </td>
        </tr>
        <tr id="editspan_<?php echo $key?>_reserved_ch" style="<?php if(isset($span['signalling']) && (($span['signalling'] != 'pri_net') || ($span['signalling'] != 'pri_cpe'))) { ?>display:none;<?php } ?>">
            <td style="width:10px;">
                <label>DChannel:</label>    	
            </td>
            <td>
                <?php echo $span['reserved_ch'];?>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_signalling">Signalling:</label>
            </td>
            <td>
                <select id="editspan_<?php echo $key?>_signalling" name="editspan_<?php echo $key?>_signalling">
            		<option value="pri_net" <?php echo set_default($span['signalling'],'pri_net'); ?>>PRI - Net</option>
            		<option value="pri_cpe" <?php echo set_default($span['signalling'],'pri_cpe'); ?>>PRI - CPE</option>
            		<option value="em" <?php echo set_default($span['signalling'],'em'); ?>>E &amp; M</option>
            		<option value="em_w" <?php echo set_default($span['signalling'],'em_w'); ?>>E &amp; M -- Wink</option>
            		<option value="featd" <?php echo set_default($span['signalling'],'featd'); ?>>E &amp; M -- fead(DTMF)</option>
            		<option value="fxo_ks" <?php echo set_default($span['signalling'],'fxo_ks'); ?>>FXOKS</option>
            		<option value="fxo_ls" <?php echo set_default($span['signalling'],'fxo_ls'); ?>>FXOLS</option>
            	</select>
            </td>
        </tr>
        <?php if ($span['totchans'] != 3 || substr($span['signalling'],0,3) == 'pri') { ?>
            <tr>
                <td style="width:10px;">
                	<label for="editspan_<?php echo $key?>_switchtype">Switchtype:</label>
                </td>
                <td>
                 	<select id="editspan_<?php echo $key?>_switchtype" name="editspan_<?php echo $key?>_switchtype">
                		<option value="national" <?php echo set_default($span['switchtype'],'national'); ?>>National ISDN 2 (default)</option>
                		<option value="dms100" <?php echo set_default($span['switchtype'],'dms100'); ?>>Nortel DMS100</option>
                		<option value="4ess" <?php echo set_default($span['switchtype'],'4ess'); ?>>AT&amp;T 4ESS</option>
                		<option value="5ess" <?php echo set_default($span['switchtype'],'5ess'); ?>>Lucent 4ESS</option>
                		<option value="euroisdn" <?php echo set_default($span['switchtype'],'euroisdn'); ?>>EuroISDN</option>
                		<option value="ni1" <?php echo set_default($span['switchtype'],'ni1'); ?>>Old National ISDN 1</option>
                		<option value="qsig" <?php echo set_default($span['switchtype'],'qsig'); ?>>Q.SIG</option>
                	</select>   
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_syncsrc">Sync/Clock Source:</label>
            </td>
            <td>
                <select id="editspan_<?php echo $key?>_syncsrc" name="editspan_<?php echo $key?>_syncsrc">
            	<?php for($i=0; $i<$dahdi_cards->get_span_count($span['location']); $i++): ?>
            		<option value="<?php echo $i?>" <?php echo set_default($span['syncsrc'],$i); ?>><?php echo $i?></option>
            	<?php endfor; ?>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_lbo">Line Build Out:</label>
            </td>
            <td>
                <select id="editspan_<?php echo $key?>_lbo" name="editspan_<?php echo $key?>_lbo">
            		<option value="0" <?php echo set_default($span['lbo'],0); ?>>0 db (CSU)/0-133 feet (DSX-1)</option>
            		<option value="1" <?php echo set_default($span['lbo'],1); ?>>133-266 feet (DSX-1)</option>
            		<option value="2" <?php echo set_default($span['lbo'],2); ?>>266-399 feet (DSX-1)</option>
            		<option value="3" <?php echo set_default($span['lbo'],3); ?>>399-533 feet (DSX-1)</option>
            		<option value="4" <?php echo set_default($span['lbo'],4); ?>>533-655 feet (DSX-1)</option>
            		<option value="5" <?php echo set_default($span['lbo'],5); ?>>-7.5db (CSU)</option>
            		<option value="6" <?php echo set_default($span['lbo'],6); ?>>-15db (CSU)</option>
            		<option value="7" <?php echo set_default($span['lbo'],7); ?>>-22.5db (CSU)</option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_pridialplan">Pridialplan:</label>
            </td>
            <td>
              	<select id="editspan_<?php echo $key?>_pridialplan" name="editspan_<?php echo $key?>_pridialplan">
            		<option value="national" <?php echo set_default($span['pridialplan'],'national'); ?>>National</option>
            		<option value="dynamic" <?php echo set_default($span['pridialplan'],'dynamic'); ?>>Dynamic</option>
            		<option value="unknown" <?php echo set_default($span['pridialplan'],'unknown'); ?>>Unknown</option>
            		<option value="local" <?php echo set_default($span['pridialplan'],'local'); ?>>Local</option>
            		<option value="private" <?php echo set_default($span['pridialplan'],'private'); ?>>Private</option>
            		<option value="international" <?php echo set_default($span['pridialplan'],'international'); ?>>International</option>
            	</select>
            </td>
        </tr>  
        <tr>
            <td style="width:10px;">
            	<label for="editspan_<?php echo $key?>_prilocaldialplan">Prilocaldialplan:</label>
            </td>
            <td>
                <select id="editspan_<?php echo $key?>_prilocaldialplan" name="editspan_<?php echo $key?>_prilocaldialplan">
            		<option value="national" <?php echo set_default($span['prilocaldialplan'],'national'); ?>>National</option>
            		<option value="dynamic" <?php echo set_default($span['prilocaldialplan'],'dynamic'); ?>>Dynamic</option>
            		<option value="unknown" <?php echo set_default($span['prilocaldialplan'],'unknown'); ?>>Unknown</option>
            		<option value="local" <?php echo set_default($span['prilocaldialplan'],'local'); ?>>Local</option>
            		<option value="private" <?php echo set_default($span['prilocaldialplan'],'private'); ?>>Private</option>
            		<option value="international" <?php echo set_default($span['prilocaldialplan'],'international'); ?>>International</option>
            	</select>
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label for="editspan_<?php echo $key?>_priexclusive">Priexclusive:</label>
            </td>
            <td>
                <select id="editspan_<?php echo $key?>_priexclusive" name="editspan_<?php echo $key?>_priexclusive">
                    <option value="" <?php echo set_default($span['priexclusive'],''); ?>></option>
                    <option value="no" <?php echo set_default($span['priexclusive'],'no'); ?>>No</option>
                    <option value="yes" <?php echo set_default($span['priexclusive'],'yes'); ?>>Yes</option>
                </select>
            </td>
        </tr>
    </table>
    <br />
    <h2>Group Settings</h2>
    <hr>
    <?php $groups = json_decode($span['additional_groups'],TRUE); 
        foreach($groups as $gkey => $data) {
    ?>
    <table width="100%" id="editspan_<?php echo $key?>_group_settings_<?php echo $gkey?>" style="text-align:left;" border="0" cellspacing="0">
        <tr>
            <td style="width:10px;">
                <label>Group: </label>
            </td>
            <td>
        	    <input type="text" id="editspan_<?php echo $key?>_group_<?php echo $gkey?>" name="editspan_<?php echo $key?>_group_<?php echo $gkey?>" size="2" value="<?php echo set_default($data['group']); ?>" />
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label>Context: </label>
            </td>
            <td>
                <input type="text" id="editspan_<?php echo $key?>_context_<?php echo $gkey?>" name="editspan_<?php echo $key?>_context_<?php echo $gkey?>" value="<?php echo set_default($data['context']); ?>" />
            </td>
        </tr>
        <tr>
            <td style="width:10px;">
                <label>Used Channels: </label>
            </td>
            <td>
                <select id="editspan_<?php echo $key?>_definedchans_<?php echo $gkey?>" name="editspan_<?php echo $key?>_definedchans_<?php echo $gkey?>">
            	<?php for($i=1; $i<=$span['totchans']; $i++) { ?>
            		<option value="<?php echo $i?>" <?php echo set_default($data['usedchans'],$i); ?>><?php echo $i?></option>
            	<?php } ?>
            	</select>
            	From: <span id="editspan_<?php echo $key?>_from_<?php echo $gkey?>"><?php echo $data['fxx'];?></span>
            	Reserved: <span id="editspan_<?php echo $key?>_reserved_<?php echo $gkey?>"><?php echo $span['reserved_ch']?></span>
            	<input type="hidden" id="editspan_<?php echo $key?>_endchan_<?php echo $gkey?>" name="editspan_<?php echo $key?>_endchan_<?php echo $gkey?>" value="<?php echo $data['endchan']; ?>" />
            	<input type="hidden" id="editspan_<?php echo $key?>_startchan_<?php echo $gkey?>" name="editspan_<?php echo $key?>_startchan_<?php echo $gkey?>" value="<?php echo $data['startchan']; ?>" />
            </td>
        </tr>
    </table>
    <?php } ?>
</form>