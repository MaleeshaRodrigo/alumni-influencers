<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Alumni Influencers API Docs</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
	<style>
		* { margin: 0; padding: 0; box-sizing: border-box; }
		body { background: #fafafa; font-family: Arial, sans-serif; }
		
		/* Navigation Bar */
		.navbar {
			background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
			color: #e2e8f0;
			padding: 0;
			display: flex;
			justify-content: space-between;
			align-items: center;
			box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		}
		
		.navbar-brand {
			font-size: 20px;
			font-weight: bold;
			padding: 16px 24px;
			display: flex;
			align-items: center;
			gap: 8px;
		}
		
		.navbar-menu {
			display: flex;
			gap: 0;
			align-items: center;
			padding-right: 24px;
		}
		
		.navbar-menu a {
			color: #cbd5e1;
			text-decoration: none;
			padding: 20px 16px;
			border-bottom: 3px solid transparent;
			transition: all 0.3s ease;
			font-size: 14px;
		}
		
		.navbar-menu a:hover {
			color: #93c5fd;
			border-bottom-color: #93c5fd;
		}
		
		.navbar-menu a.active {
			color: #93c5fd;
			border-bottom-color: #3b82f6;
			background: rgba(59, 130, 246, 0.1);
		}
		
		.navbar-menu a.btn-primary {
			background: #3b82f6;
			border: none;
			border-radius: 4px;
			padding: 8px 16px;
			margin-left: 8px;
			transition: background 0.3s ease;
		}
		
		.navbar-menu a.btn-primary:hover {
			background: #2563eb;
			border-bottom: none;
			color: white;
		}
		
		.top-note {
			padding: 12px 24px;
			background: #1e293b;
			color: #cbd5e1;
			font-size: 13px;
			border-bottom: 1px solid #334155;
		}
		
		.top-note strong { color: #93c5fd; }
		.top-note a { color: #60a5fa; text-decoration: none; }
		.top-note a:hover { text-decoration: underline; }
		
		#swagger-ui {
			margin-top: 0;
		}
	</style>
</head>
<body>
	<!-- Navigation Bar -->
	<div class="navbar">
		<div class="navbar-brand">
			🎓 Alumni Influencers
		</div>
		<div class="navbar-menu">
			<a href="<?php echo site_url('/'); ?>">Home</a>
			<a href="<?php echo site_url('dashboard'); ?>">Dashboard</a>
			<a href="<?php echo site_url('dashboard/login'); ?>">Login</a>
			<a href="<?php echo site_url('dashboard/register'); ?>" class="btn-primary">Register</a>
		</div>
	</div>
	
	<!-- Info Note -->
	<div class="top-note">
		<strong>📚 API Documentation</strong> - Raw OpenAPI spec: <a href="<?php echo site_url('api-docs/openapi.yaml'); ?>"><?php echo site_url('api-docs/openapi.yaml'); ?></a>
	</div>
	
	<!-- Swagger UI Container -->
	<div id="swagger-ui"></div>

	<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
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
