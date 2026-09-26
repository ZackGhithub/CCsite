/**
 * Bloc "cc/tri-panel" — conteneur de panneaux en grille (3 ou 4 colonnes).
 */
( function ( blocks, blockEditor, element, components, i18n ) {
	var el = element.createElement;
	var useBlockProps = blockEditor.useBlockProps;
	var InnerBlocks = blockEditor.InnerBlocks;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var __ = i18n.__;

	function panelClassName( columns ) {
		return 4 === columns ? 'cc-tri-panel cc-tri-panel--quatre' : 'cc-tri-panel';
	}

	blocks.registerBlockType( 'cc/tri-panel', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( { className: panelClassName( attributes.columns ) } );

			return el(
				'div',
				blockProps,
				el( InspectorControls, {},
					el( PanelBody, { title: __( 'Disposition', 'cours-chambertin' ) },
						el( SelectControl, {
							label: __( 'Nombre de colonnes', 'cours-chambertin' ),
							value: attributes.columns,
							options: [
								{ label: __( '3 colonnes', 'cours-chambertin' ), value: 3 },
								{ label: __( '4 colonnes', 'cours-chambertin' ), value: 4 },
							],
							onChange: function ( value ) {
								setAttributes( { columns: Number( value ) } );
							},
						} )
					)
				),
				el( InnerBlocks, {
					allowedBlocks: [ 'cc/tri-panel-side' ],
					template: [
						[ 'cc/tri-panel-side' ],
						[ 'cc/tri-panel-side' ],
						[ 'cc/tri-panel-side' ],
					],
					orientation: 'horizontal',
				} )
			);
		},
		save: function ( props ) {
			var blockProps = useBlockProps.save( { className: panelClassName( props.attributes.columns ) } );
			return el( 'div', blockProps, el( InnerBlocks.Content ) );
		},
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.components, window.wp.i18n );
