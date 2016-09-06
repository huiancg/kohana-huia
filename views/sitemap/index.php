<?php echo '<?xml version="1.0" encoding="' . Kohana::$charset . '"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach($urls as $url) : ?>
<url>
	<loc><?php echo Arr::get($url, 'loc'); ?></loc>
<?php if (Arr::get($url, 'lastmod')) : ?>
	<lastmod><?php echo Arr::get($url, 'lastmod'); ?></lastmod>
<?php endif; ?>
</url>
<?php endforeach; ?>
</urlset>