<?php get_header(); ?>
<?php \Yoast\YoastCom\Theme\get_template_part( 'html_includes/siteheader', [ 'academy-sub' => true ] ); ?>

<div class="site">
<div class="row">
	<?php \Yoast\YoastCom\Theme\get_template_part( 'html_includes/partials/breadcrumbs' ); ?>
<main role="main">
	<div class="row">
		<div class="search-header">
			<h1><?php echo esc_html( get_the_archive_title() ); ?></h1>
			<div id="algolia-search-box">
				<div id="algolia-stats"></div>
				<svg class="search-icon" width="25" height="25" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path d="M24.828 31.657a16.76 16.76 0 0 1-7.992 2.015C7.538 33.672 0 26.134 0 16.836 0 7.538 7.538 0 16.836 0c9.298 0 16.836 7.538 16.836 16.836 0 3.22-.905 6.23-2.475 8.79.288.18.56.395.81.645l5.985 5.986A4.54 4.54 0 0 1 38 38.673a4.535 4.535 0 0 1-6.417-.007l-5.986-5.986a4.545 4.545 0 0 1-.77-1.023zm-7.992-4.046c5.95 0 10.775-4.823 10.775-10.774 0-5.95-4.823-10.775-10.774-10.775-5.95 0-10.775 4.825-10.775 10.776 0 5.95 4.825 10.775 10.776 10.775z" fill-rule="evenodd"></path></svg>
			</div>
		</div>
		<div id="ais-wrapper">
			<aside id="ais-facets">
				<section class="ais-facets" id="facet-post-types"></section>
				<section class="ais-facets" id="facet-categories"></section>
				<section class="ais-facets" id="facet-tags"></section>
				<section class="ais-facets" id="facet-users"></section>
			</aside>
			<div>
			<div id="algolia-hits"></div>
			<div id="algolia-pagination"></div>
			</div>
			</div>
	</div>

</main>
</div>
</div>
<?php \Yoast\YoastCom\Theme\get_template_part( 'html_includes/partials/newsletter-subscribe' ); ?>
<div class="rowholder">
	<?php \Yoast\YoastCom\Theme\get_template_part( 'html_includes/fullfooter' ); ?>
</div>

	<script type="text/html" id="tmpl-instantsearch-hit">
		<article itemtype="http://schema.org/Article">
			<div class="result row">
				<h2 class="tight" itemprop="name headline">
					<a href="{{ data.permalink }}" title="{{ data.post_title }}" itemprop="url">
						{{{ data._highlightResult.post_title.value }}}
					</a>
				</h2>

				<div class="meta">
					<p class="ais-hits--tags">
						{{{data.post_modified_formatted}}}
						<# if (data.post_author.display_name) { #>
						by
						<a href="{{{data.post_author.author_link}}}">
							{{{data.post_author.display_name}}}
						</a>
						<# } #>
					</p>
					<# if (data.taxonomies.post_tag) { #>
					<p>
						<# for (var index in data.taxonomies.post_tag) { #>
						<span class="ais-hits--tag">{{{ data._highlightResult.taxonomies.post_tag[index].value }}}</span>
						<# } #>
					</p>
					<# } #>
				</div>

				<# if ( data.images.thumbnail || data.metadesc || data.excerpt ) { #>
				<div class="media media--nofloat">
					<# if ( data.images.thumbnail ) { #>
					<a href="{{{data.permalink}}}" class="imgExt">
						<img src="{{ data.images.thumbnail.url }}" class="ais-hits--thumbnail attachment-thumbnail-recent-articles size-thumbnail-recent-articles wp-post-image" width="250" height="131" alt="{{ data.post_title }}" title="{{ data.post_title }}" itemprop="image"/>
					</a>
					<# } #>
					<div class="bd metadesc">
						<# if(data.metadesc){ #>
						<p class="metadesc">{{{data.metadesc}}}</p>
						<# } else if(data.excerpt) { #>
						<p class="excerpt">{{{data.excerpt}}}</p>
						<# } #>
					</div>
				</div>
				<# } #>
			</div>
		</article>
		<hr class="hr--no-pointer">
	</script>


	<script type="text/javascript">
		jQuery(function() {
			if(jQuery('#algolia-search-box').length > 0) {

				if (algolia.indices.searchable_posts === undefined && jQuery('.admin-bar').length > 0) {
					alert('It looks like you haven\'t indexed the searchable posts index. Please head to the Indexing page of the Algolia Search plugin and index it.');
				}

				/* Instantiate instantsearch.js */
				var search = instantsearch({
					appId: algolia.application_id,
					apiKey: algolia.search_api_key,
					indexName: algolia.indices.searchable_posts.name,
					urlSync: {
						mapping: {'q': 's'},
						trackedParameters: ['query']
					},
					searchParameters: {
						facetingAfterDistinct: true,
			highlightPreTag: '__ais-highlight__',
			highlightPostTag: '__/ais-highlight__'
					}
				});

				/* Search box widget */
				search.addWidget(
					instantsearch.widgets.searchBox({
						container: '#algolia-search-box',
						placeholder: 'Search for...',
						wrapInput: false,
						poweredBy: algolia.powered_by_enabled
					})
				);

				/* Stats widget */
				search.addWidget(
					instantsearch.widgets.stats({
						container: '#algolia-stats'
					})
				);

				/* Hits widget */
				search.addWidget(
					instantsearch.widgets.hits({
						container: '#algolia-hits',
						hitsPerPage: 10,
						templates: {
							empty: 'No results were found for "<strong>{{query}}</strong>".',
							item: wp.template('instantsearch-hit')
						},
						transformData: {
							item: function (hit) {

								function replace_highlights_recursive (item) {
								  if( item instanceof Object && item.hasOwnProperty('value')) {
									  item.value = _.escape(item.value);
									  item.value = item.value.replace(/__ais-highlight__/g, '<em>').replace(/__\/ais-highlight__/g, '</em>');
								  } else {
									  for (var key in item) {
										  item[key] = replace_highlights_recursive(item[key]);
									  }
								  }
								  return item;
								}

								hit._highlightResult = replace_highlights_recursive(hit._highlightResult);
								hit._snippetResult = replace_highlights_recursive(hit._snippetResult);

								return hit;
							}
						}
					})
				);

				/* Pagination widget */
				search.addWidget(
					instantsearch.widgets.pagination({
						container: '#algolia-pagination'
					})
				);

				// Post types refinement widget
				search.addWidget(
					instantsearch.widgets.refinementList( {
						container: "#facet-post-types",
						attributeName: "post_type_label",
						operator: "or",
						limit: 10,
						sortBy: [ "count:desc", "name:asc" ],
						templates: {
							header: "<h3 class=\"widgettitle\">Post types</h3>"
						},
						collapsible: true,
					} )
				);

				// Categories refinement widget
				search.addWidget(
					instantsearch.widgets.refinementList({
						container: "#facet-categories",
						attributeName: "taxonomies.category",
						operator: "or",
						limit: 10,
						sortBy: [ "count:desc", "name:asc" ],
						templates: {
							header: "<h3 class=\"widgettitle\">Categories</h3>"
						},
						collapsible: true,
					})
				);

				// Author refinement widget
				search.addWidget(
					instantsearch.widgets.refinementList({
						container: "#facet-users",
						attributeName: "post_author.display_name",
						operator: "or",
						limit: 10,
						sortBy: [ "count:desc", "name:asc" ],
						templates: {
							header: "<h3 class=\"widgettitle\">Authors</h3>"
						},
						collapsible: true,
					})
				);

				/* Start */
				search.start();

				jQuery('#algolia-search-box input').attr('type', 'search').select();
			}
		});
	</script>

<?php get_footer(); ?>
