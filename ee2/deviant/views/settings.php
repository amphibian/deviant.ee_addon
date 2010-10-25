<?php

    $this->EE =& get_instance();
	$this->EE->cp->load_package_css('settings');
	$this->EE->cp->load_package_js('settings');
?>

<?php foreach ($cp_messages as $cp_message_type => $cp_message) : ?>
	<p class="notice <?=$cp_message_type?>"><?=$cp_message?></p>
<?php endforeach; ?>

<?php
	// We need a hidden field called 'file' whose value matches this extension's url slug. (Apparently?)
	echo form_open('C=addons_extensions'.AMP.'M=save_extension_settings', array('id' => $file), array('file' => $file));
?>

<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	
	<thead>
		<tr>
			<th><?php echo $this->EE->lang->line('channel'); ?></th>
			<th><?php echo $this->EE->lang->line('redirect_after_new'); ?></th>
			<th><?php echo $this->EE->lang->line('redirect_after_update'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr class="deviant-global">
			<td><?php echo $this->EE->lang->line('global_redirect'); ?></td>
			<td>
				<?php echo form_dropdown(
					'global_new_deviant_'.$site, $global_locations, (isset($current['global_new_deviant_'.$site])) ? $current['global_new_deviant_'.$site] : 'none'
				); ?>
			</td>
			<td>
				<?php echo form_dropdown(
					'global_updated_deviant_'.$site, $global_locations, (isset($current['global_updated_deviant_'.$site])) ? $current['global_updated_deviant_'.$site] : 'none'
				); ?>
			</td>
		</tr>
	</tbody>
	<tbody>
	<?php
		$i = 0;
		foreach($channels as $id => $title)
		{
			$i++;
			$new_extra = $updated_extra = '';
			if(isset($current['global_new_deviant_'.$site]) && $current['global_new_deviant_'.$site] != 'none')
			{
				$new_selected = $current['global_new_deviant_'.$site];
				$new_extra = 'disabled="disabled"';
			}
			elseif(isset($current['new_channel_id_'.$id]))
			{
				$new_selected = $current['new_channel_id_'.$id];
			}
			else
			{
				$new_selected = 'default';
			}
			if(isset($current['global_updated_deviant_'.$site]) && $current['global_updated_deviant_'.$site] != 'none')
			{
				$updated_selected = $current['global_updated_deviant_'.$site];
				$updated_extra = 'disabled="disabled"';
			}
			elseif(isset($current['updated_channel_id_'.$id]))
			{
				$updated_selected = $current['updated_channel_id_'.$id];
			}
			else
			{
				$updated_selected = 'default';
			}
	?>
		<tr class="<?php echo ($i % 2 == 0) ? 'even' : 'odd'; ?>">
			<td><?php echo $title; ?></td>
			<td>
				<?php echo form_dropdown('new_channel_id_'.$id, $locations, $new_selected, $new_extra); ?>
			</td>
			<td>
				<?php echo form_dropdown('updated_channel_id_'.$id, $locations, $updated_selected, $updated_extra); ?>
			</td>
		</tr>
	<?php
		}	
	?>	
	</tbody>
	</table>
	
<?php
	echo form_submit(
		array(
			'name' => 'submit',
			'value' => $this->EE->lang->line('save_settings'),
			'class' => 'submit'
		)
	);
	echo form_close();
?>