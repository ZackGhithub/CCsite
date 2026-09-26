/**
 * Bloc "cc/timeline-item" — une étape de la frise (<li> avec date, titre,
 * et un corps en blocs Paragraphe natifs).
 */
( function ( blocks, blockEditor, element, components, i18n ) {
	var el = element.createElement;
	var useBlockProps = blockEditor.useBlockProps;
	var RichText = blockEditor.RichText;
	var InnerBlocks = blockEditor.InnerBlocks;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var ToggleControl = components.ToggleControl;
	var __ = i18n.__;

	blocks.registerBlockType( 'cc/timeline-item', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( {
				className: attributes.future ? 'cc-avenir' : undefined,
			} );

			return el(
				'li',
				blockProps,
				el( InspectorControls, {},
					el( PanelBody, { title: __( 'Réglages de l’étape', 'cours-chambertin' ) },
						el( ToggleControl, {
							label: __( 'Étape à venir (marqueur en pointillé)', 'cours-chambertin' ),
							checked: !! attributes.future,
							onChange: function ( value ) {
								setAttributes( { future: value } );
							},
						} )
					)
				),
				el( RichText, {
					tagName: 'div',
					className: 'cc-timeline-date',
					value: attributes.date,
					onChange: function ( value ) {
						setAttributes( { date: value } );
					},
					placeholder: __( 'Date (ex. 1982, Étape 1…)', 'cours-chambertin' ),
					allowedFormats: [],
				} ),
				el(
					'div',
					{ className: 'cc-timeline-body' },
					el( RichText, {
						tagName: 'h3',
						value: attributes.title,
						onChange: function ( value ) {
							setAttributes( { title: value } );
						},
						placeholder: __( 'Titre de l’étape', 'cours-chambertin' ),
					} ),
					el( InnerBlocks, {
						allowedBlocks: [ 'core/paragraph' ],
						template: [ [ 'core/paragraph' ] ],
					} )
				)
			);
		},
		save: function ( props ) {
			var attributes = props.attributes;
			var blockProps = useBlockProps.save( {
				className: attributes.future ? 'cc-avenir' : undefined,
			} );
			return el(
				'li',
				blockProps,
				el( RichText.Content, { tagName: 'div', className: 'cc-timeline-date', value: attributes.date } ),
				el(
					'div',
					{ className: 'cc-timeline-body' },
					el( RichText.Content, { tagName: 'h3', value: attributes.title } ),
					el( InnerBlocks.Content )
				)
			);
		},
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.components, window.wp.i18n );
