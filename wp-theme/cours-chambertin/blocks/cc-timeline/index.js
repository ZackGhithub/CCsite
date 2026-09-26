/**
 * Bloc "cc/timeline" — conteneur de la frise chronologique (<ol class="cc-timeline">).
 * Écrit en JS natif (sans étape de build) : dépendances déclarées côté PHP
 * via wp_register_script() dans functions.php.
 */
( function ( blocks, blockEditor, element ) {
	var el = element.createElement;
	var useBlockProps = blockEditor.useBlockProps;
	var InnerBlocks = blockEditor.InnerBlocks;

	blocks.registerBlockType( 'cc/timeline', {
		edit: function () {
			var blockProps = useBlockProps( { className: 'cc-timeline' } );
			return el(
				'ol',
				blockProps,
				el( InnerBlocks, {
					allowedBlocks: [ 'cc/timeline-item' ],
					template: [ [ 'cc/timeline-item' ] ],
					templateInsertUpdatesSelection: false,
				} )
			);
		},
		save: function () {
			var blockProps = useBlockProps.save( { className: 'cc-timeline' } );
			return el( 'ol', blockProps, el( InnerBlocks.Content ) );
		},
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element );
