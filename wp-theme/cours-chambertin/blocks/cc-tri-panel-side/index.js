/**
 * Bloc "cc/tri-panel-side" — un panneau individuel : titre + paragraphes
 * (blocs natifs). Le bouton éventuel n'est pas un bloc à part : c'est un
 * paragraphe portant la classe CSS additionnelle "cc-bento-action" avec un
 * lien sur tout son texte (réglable dans le panneau "Avancé" du bloc
 * Paragraphe natif) — la feuille de style du thème le rend comme un bouton.
 */
( function ( blocks, blockEditor, element, components, i18n ) {
	var el = element.createElement;
	var useBlockProps = blockEditor.useBlockProps;
	var RichText = blockEditor.RichText;
	var InnerBlocks = blockEditor.InnerBlocks;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var __ = i18n.__;

	function sideClassName( accent ) {
		return accent ? 'cc-tri-panel-side cc-tri-panel-side--' + accent : 'cc-tri-panel-side';
	}

	blocks.registerBlockType( 'cc/tri-panel-side', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( { className: sideClassName( attributes.accent ) } );

			return el(
				'div',
				blockProps,
				el( InspectorControls, {},
					el( PanelBody, { title: __( 'Couleur d’accent', 'cours-chambertin' ) },
						el( SelectControl, {
							label: __( 'Accent', 'cours-chambertin' ),
							value: attributes.accent,
							options: [
								{ label: __( 'Aucun (bordeaux par défaut)', 'cours-chambertin' ), value: '' },
								{ label: __( 'École (bleu)', 'cours-chambertin' ), value: 'ecole' },
								{ label: __( 'Collège (bordeaux)', 'cours-chambertin' ), value: 'college' },
							],
							onChange: function ( value ) {
								setAttributes( { accent: value } );
							},
						} )
					)
				),
				el( RichText, {
					tagName: 'h3',
					value: attributes.title,
					onChange: function ( value ) {
						setAttributes( { title: value } );
					},
					placeholder: __( 'Titre du panneau', 'cours-chambertin' ),
				} ),
				el( InnerBlocks, {
					allowedBlocks: [ 'core/paragraph' ],
					template: [ [ 'core/paragraph' ] ],
				} )
			);
		},
		save: function ( props ) {
			var attributes = props.attributes;
			var blockProps = useBlockProps.save( { className: sideClassName( attributes.accent ) } );
			return el(
				'div',
				blockProps,
				el( RichText.Content, { tagName: 'h3', value: attributes.title } ),
				el( InnerBlocks.Content )
			);
		},
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.components, window.wp.i18n );
