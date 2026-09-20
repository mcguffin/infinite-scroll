import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { InspectorControls } from '@wordpress/block-editor';
import { ExternalLink, PanelBody, PanelRow, ToggleControl, TextControl, SelectControl } from '@wordpress/components';
import { __experimentalToggleGroupControl as ToggleGroupControl} from '@wordpress/components';
import { __experimentalToggleGroupControlOption as ToggleGroupControlOption } from '@wordpress/components';

addFilter(
	'editor.BlockEdit',
	'mcguffin/infinite-scroll',
	BlockEdit => {
		return ( props ) => {
			const { name, attributes, setAttributes } = props;

			// Early return if the block is not the Image block.
			if ( name !== 'core/post-template' ) {
				return <BlockEdit { ...props } />;
			}

			// Retrieve selected attributes from the block.
			const { alt, isInfiniteScroll, loadTrigger, loadPlaceholder, buttonLabel, buttonStyle } = attributes;

			const buttonStyles = [
				...wp.blocks.getBlockType('core/button').styles,
				...infiniteScrollEdit.buttonStyles
			];

			return (
				<>
					<BlockEdit { ...props } />
					<InspectorControls>
						<PanelBody
							title={ __(
								'Navigation',
								'enable-infinite-scroll'
							) }
						>
							<PanelRow>
								<ToggleControl
									label={ __(
										'Enable Infinite Scroll',
										'infinite-scroll'
									) }
									checked={ isInfiniteScroll }
									onChange={ () => {
										setAttributes( {
											isInfiniteScroll: ! isInfiniteScroll
										} );
									} }
								/>
							</PanelRow>

							{
								isInfiniteScroll && (
									<>
										<PanelRow>
											<ToggleGroupControl
												label={ __(
													'Trigger',
													'infinite-scroll'
												) }
												value={ loadTrigger }
												onChange={ ( value ) => setAttributes( { loadTrigger: value } ) }
												isBlock
											>
												<ToggleGroupControlOption value="scroll" label={ __("Scroll","infinite-scroll") } />
												<ToggleGroupControlOption value="button" label={ __("Button","infinite-scroll") } />
											</ToggleGroupControl>
										</PanelRow>
										<PanelRow>
											<ToggleGroupControl
												label={ __(
													'Loading animation',
													'infinite-scroll'
												) }
												value={ loadPlaceholder }
												onChange={ ( value ) => setAttributes( { loadPlaceholder: value } ) }
												isBlock
											>
												<ToggleGroupControlOption value="none" label={ __("None","infinite-scroll") } />
												<ToggleGroupControlOption value="ghost" label={ __("Ghost","infinite-scroll") } />
												<ToggleGroupControlOption value="spinner" label={ __("Spinner","infinite-scroll") } />
											</ToggleGroupControl>
										</PanelRow>
										{
											'button' === loadTrigger && (
												<>
												<PanelRow>
													<TextControl
														label={ __("Button Label","infinite-scroll") }
														placeholder={ __("Load more","infinite-scroll") }
														value={ buttonLabel }
														onChange={ ( value ) => setAttributes( { buttonLabel: value } ) }
													/>
												</PanelRow>
												<PanelRow>
													<SelectControl
														label={ <span style={ {"whiteSpace": "nowrap"} }>{ __("Button style","infinite-scroll") }</span> }
														value={ buttonStyle }
														onChange={ ( value ) => setAttributes( { buttonStyle: value } ) }
														options={ buttonStyles.map( ({name,label}) => { return { label, "value": name } } ) }

													/>
												</PanelRow>
												</>
											)
										}
									</>
								)
							}

						</PanelBody>
					</InspectorControls>
				</>
			);
		};
	}
);
