import { useBlockProps } from '@wordpress/block-editor';
import { InnerBlocks } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const { generatedShortcode } = attributes;

    return (
        <div {...useBlockProps.save()}>
            {generatedShortcode}
            <InnerBlocks.Content />
        </div>
    );
}
