<div class="grid_16">
	<h2 id="page-heading">Site Metrics</h2>
</div>

<div class="clear"></div>
	
<div class="grid_12">
	<table>
		<thead>
			<tr>
				<th>IP</th>
				<th>URL</th>
				<th>Created</th>
			</tr>
		</thead>
		<?php foreach($documents as $document) { ?>
		<tr>
			<td>
				<?=substr($document->_id, 0, strpos($document->_id, '@')); ?>
			</td>
			<td>
				<?=$this->html->link($document->url, array('controller' => 'metrics', 'action' => 'url', 'admin' => true, 'args' => array($this->html->urlAsArg($document->url)))); ?>
			</td>
			<td>
				<?=$this->html->date($document->c->sec); ?>
			</td>
		</tr>
		<?php } ?>
	</table>

<?=$this->Paginator->paginate(); ?>
<em>Showing page <?=$page; ?> of <?=$total_pages; ?>. <?=$total; ?> total record<?php echo ((int) $total > 1 || (int) $total == 0) ? 's':''; ?>.</em>
</div>

<div class="grid_4">
	<div class="box">
		<h2>Search for Metrics by URL</h2>
		<div class="block">
			<?=$this->html->query_form(array('label' => 'Query ')); ?>
		</div>
	</div>
</div>

<div class="clear"></div>