import { useBlockProps } from '@wordpress/block-editor';
import { InnerBlocks } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const { generatedShortcode } = attributes;

    // Split the generated shortcode to isolate the tag name (e.g., "fungate")
    const shortcodeTag = generatedShortcode.match(/^\[([^\s]+)/)[1];

    return (
        <div {...useBlockProps.save()}>
            {/* Opening shortcode tag */}
            <div dangerouslySetInnerHTML={{ __html: generatedShortcode }}></div>
            <InnerBlocks.Content />
            {/* Closing shortcode tag */}
            <div dangerouslySetInnerHTML={{ __html: `[/${shortcodeTag}]` }}></div>
        </div>
    );
}
