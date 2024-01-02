/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
import { useState, useEffect } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { Button, TextControl, PanelBody, PanelRow, CheckboxControl } from '@wordpress/components';
import { get } from '@wordpress/url';

const CustomMediaPicker = ({ onFileSelect, selectedFile }) => {
    const [files, setFiles] = useState([]);

    useEffect(() => {
        fetch('/wp-json/fungate/v1/list-media', {
            method: 'GET',
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce
            }
        })
        .then(response => response.json())
        .then(data => {
            const filesArray = Object.values(data);
            setFiles(filesArray);
        })
        .catch(error => console.error('Error:', error));
    }, []);

    return (
        <select 
            value={selectedFile} 
            onChange={(e) => onFileSelect(e.target.value)}
            style={{ width: '100%', padding: '8px', marginBottom: '20px' }}
        >
            <option value="">Select a file</option>
            {files.map(file => (
                <option key={file} value={file}>
                    {file.split('/').pop()} {/* Displaying just the file name */}
                </option>
            ))}
        </select>
    );
};

const getFileType = (fileName) => {
    if (!fileName) return 'unknown'; // Return 'unknown' or a default type if fileName is undefined

    const extension = fileName.split('.').pop().toLowerCase();
    if (['mp3', 'wav', 'ogg', 'm4a'].includes(extension)) {
        return 'audio';
    } else if (['mp4', 'webm', 'ogv'].includes(extension)) {
        return 'video';
    } else {
        return 'image';
    }
};
export default function Edit({ attributes, setAttributes }) {
    const { src, contract, minter, nft, schedule, showDownloadButton } = attributes;
    
    useEffect(() => {
        const generateShortcode = () => {
            const fileType = src ? getFileType(src) : '';
            const shortcode = `[fungate_media src="${src || ''}" type="${fileType}" contract="${contract || ''}" minter="${minter || ''}" nft="${nft || ''}" schedule="${schedule || ''}"]`;
            setAttributes({ generatedShortcode: shortcode });
        };
        generateShortcode();
    }, [src, contract, minter, nft, schedule, showDownloadButton]);
    
    // File selection handler remains the same
    const handleFileSelect = (fileName) => {
		setAttributes({ src: fileName });
	};
    
	const handleFileUpload = (event) => {
        const file = event.target.files && event.target.files[0];
        if (!file) {
            console.error('No file provided');
            return;
        }
    
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_wpnonce', wpApiSettings.nonce);
    
        fetch('/wp-json/fungate/v1/upload-media', {
            method: 'POST',
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && data.url) {
                setAttributes({ src: data.url });
            }
        })
        .catch(error => console.error('Error:', error));
    };
    
	

    return (
        <div {...useBlockProps()}>
            {/* Block Content: File Picker */}
			<div>
            Upload File:
            <input type="file" id="fungate-media-Input" onChange={(e) => handleFileUpload(e)} />
        </div>
        <div>Select file:
		<CustomMediaPicker onFileSelect={handleFileSelect} selectedFile={src} />
        </div>
			{/* Display the selected file name */}
            {src && <p>Selected File: {src.split('/').pop()}</p>}

            {/* Sidebar Controls */}
            <InspectorControls>
                <PanelBody title="Settings">
                    <PanelRow>
                        <TextControl
                            label="Contract"
                            value={contract}
                            onChange={(value) => setAttributes({ contract: value })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label="Minter"
                            value={minter}
                            onChange={(value) => setAttributes({ minter: value })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label="NFT"
                            value={nft}
                            onChange={(value) => setAttributes({ nft: value })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label="Schedule"
                            value={schedule}
                            onChange={(value) => setAttributes({ schedule: value })}
                        />
                    </PanelRow>
                    <PanelRow>
                        <CheckboxControl
                            label="Show Download Button"
                            checked={showDownloadButton}
                            onChange={(value) => setAttributes({ showDownloadButton: value })}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
        </div>
    );
}
