<?php
	/**
	 * This is a Backbone.js template
	 */
?>

<# var thumbnail = data.sizes.thumbnail || data.sizes.medium || data.sizes.large || data.sizes.full || { url: data.url } #>

<div class="thumbnail" data-id="{{ data.id }}">
	<a href="#" class="delete-button"></a>
	<img src="{{ thumbnail.url }}" alt="{{ data.alt }}" />
</div>