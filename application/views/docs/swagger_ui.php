<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Alumni Influencers API Docs</title>
	<link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
	<style>
		body { margin: 0; background: #fafafa; }
		.top-note {
			padding: 10px 16px;
			background: #0f172a;
			color: #e2e8f0;
			font-family: Arial, sans-serif;
			font-size: 14px;
		}
		.top-note a { color: #93c5fd; }
	</style>
</head>
<body>
	<div class="top-note">
		Swagger UI OpenAPI spec.
		Raw spec: <a href="<?php echo site_url('api-docs/openapi.yaml'); ?>"><?php echo site_url('api-docs/openapi.yaml'); ?></a>
	</div>
	<div id="swagger-ui"></div>

	<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
	<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
	<script>
		window.onload = function () {
			window.ui = SwaggerUIBundle({
				url: "<?php echo site_url('api-docs/openapi.yaml'); ?>",
				dom_id: '#swagger-ui',
				deepLinking: true,
				presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
				layout: "BaseLayout"
			});
		};
	</script>
</body>
</html>
