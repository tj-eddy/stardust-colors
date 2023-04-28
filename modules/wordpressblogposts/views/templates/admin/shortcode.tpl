<div id="shortcode-widget" style="background-color:#f1f1f1; padding:10px; border:1px solid #ccc;">
	[wordpressblogposts limit=<span class="limit">0</span> categories='<span class="categories"></span>']
</div>

<script>
	$(document).ready(function() {

		var $widget = $("#shortcode-widget");

		$("input#wbp_limit").change(function() {
			$widget.find(".limit").html($(this).val());
		});

		$("input#wbp_category_filter").change(function () {
			$widget.find(".categories").html($(this).val());
		});

	});
</script>