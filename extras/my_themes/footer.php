<hr class="xpress-border"></hr>

<div id="xpress_footer">
	<div class="xpress_rss">
		<?php printf(__('%1$s and %2$s.', 'xpress'), '<a href="' . get_bloginfo('rss2_url') . '">' . __('Entries (RSS)', 'xpress') . '</a>', '<a href="' . get_bloginfo('comments_rss2_url') . '">' . __('Comments (RSS)', 'xpress') . '</a>'); ?>
	</div>
	<div class="xpress_credit"><?php echo xpress_credit('echo=0'). ' (' . xpress_convert_time('echo=0&format=' . __('%.3f sec.', 'xpress')) . ')'; ?></div>
</div>
</div>
		<?php wp_footer(); ?>
</body>
</html>
