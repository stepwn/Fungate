import { RawHTML } from '@wordpress/element';

export default function save({ attributes }) {
    const { generatedShortcode } = attributes;
    // Save the already generated shortcode
    return <RawHTML>{generatedShortcode}</RawHTML>;
}
