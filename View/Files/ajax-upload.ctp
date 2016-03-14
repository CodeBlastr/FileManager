<?php foreach($files as $item): ?>
<li class="span2 media-item">
	<a href="#" class="thumbnail">
		<?php echo $this->File->display($item, array('width' => 100, 'height' => 100)); ?>
		<p style="text-align: center;"><?php echo $item['Myfile']['title']; ?></p>
	</a>
</li>
<?php endforeach; ?>