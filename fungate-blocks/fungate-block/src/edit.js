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

import { useState, useEffect } from '@wordpress/element';
import { InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, Button, Modal, TextControl,CheckboxControl, SelectControl } from '@wordpress/components';



export default function Edit({ attributes, setAttributes }) {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [dateGroups, setDateGroups] = useState([createInitialDateGroup()]);
    const [nftUrl, setNftUrl] = useState('');
    const [anyInCollection, setAnyInCollection] = useState(false);

    const generateShortcode = () => {
        // Construct the shortcode using current attributes
        const shortcodeAttrs = {
            chain: attributes.chain,
            minter: attributes.minter,
            contract: attributes.contract,
            nft_id: attributes.nft_id,
            schedule: attributes.schedule,
            // Add any other attributes you need to include in the shortcode
        };
    
        const shortcode = `[fungate ${Object.entries(shortcodeAttrs)
            .filter(([key, value]) => value)
            .map(([key, value]) => `${key}="${value}"`)
            .join(' ')}]`;
    
        // Update the attribute
        setAttributes({ generatedShortcode: shortcode });
        console.log("Updated Attribute: ", attributes.generatedShortcode);
    };
    
    // Update the shortcode whenever relevant attributes change
    useEffect(() => {
        generateShortcode();
    }, [attributes.chain, attributes.minter, attributes.contract, attributes.nft_id, attributes.schedule]);
    
    const extractNftDetails = (url) => {
        // Regex for Etherscan
        const etherscanRegex = /https:\/\/etherscan\.io\/nft\/(0x[a-fA-F0-9]+)\/(\d+)/;
        // Adjusted Regex for Loopring Explorer
        const loopringRegex = /https:\/\/explorer\.loopring\.io\/nft\/(0x[a-fA-F0-9]+)-\d+-(0x[a-fA-F0-9]+)-(0x[a-fA-F0-9]+)-\d+/;
    
        let matches;
        let details = {};
    
        // Check if the URL matches Etherscan pattern
        matches = url.match(etherscanRegex);
        if (matches) {
            const [, contract, nftId] = matches;
            details = {
                contract: contract,
                nft_id: nftId,
                minter: '', // Etherscan doesn't provide minter in the URL
            };
        } else {
            // Check if the URL matches Loopring pattern
            matches = url.match(loopringRegex);
            if (matches) {
                const [, minter, contract, nftId] = matches;
                details = {
                    contract: contract,
                    nft_id: nftId,
                    minter: minter,
                };
            }
        }
    
        return details;
    };
    
    
    
    
    
    const handleNftUrlChange = (url) => {
        setNftUrl(url);
        extractNftDetails(url);
    };
     // Updated state to manage the NFT collection option
     const [nftCollectionOption, setNftCollectionOption] = useState('specific');

     // Update the function to handle changes in the SelectControl
     const handleNftCollectionOptionChange = (selectedOption) => {
         setNftCollectionOption(selectedOption);
 
         // Update the attributes based on the selected option
         if (selectedOption === 'anyInCollection') {
             setAttributes({ nft_id: '' });
             setAttributes({ minter: '' });
         } else if (selectedOption === 'anyByMinter') {
             setAttributes({ contract: '' });
             setAttributes({ nft_id: '' });
         }
         else if (selectedOption === 'specific') {
            setAttributes({ minter: '' });
        }
     };
    
    
     function createInitialDateGroup() {
        return {
            date: new Date().toISOString().slice(0, 10),
            time: '00:00',
            minter: '',
            nft_id: '',
            contract: '',
            nftUrl: '',
            anyInCollection: false,
            nftCollectionOption: 'specific', // Add this line
        };
    }
    
    function handleAddDateGroup() {
        setDateGroups([...dateGroups, createInitialDateGroup()]);
    }

    function handleRemoveDateGroup(index) {
        const newDateGroups = [...dateGroups];
        newDateGroups.splice(index, 1);
        setDateGroups(newDateGroups);
    }

    function handleDateGroupChange(index, key, value) {
        const newDateGroups = [...dateGroups];
        if (key === 'nftUrl') {
            // Extract NFT details if the NFT URL changes
            const details = extractNftDetails(value);
            newDateGroups[index].nft_id = details.nft_id || '';
            newDateGroups[index].contract = details.contract || '';
            newDateGroups[index].minter = details.minter || '';
        } else if (key === 'anyInCollection') {
            // Clear the NFT ID if "any in collection" is checked
            newDateGroups[index].contract = value ? '' : newDateGroups[index].contract;
            newDateGroups[index].minter = '';
            newDateGroups[index].nft_id = '';
        }
        else if (key === 'anyByMinter') {
            // Clear the NFT ID if "any in collection" is checked
            newDateGroups[index].minter = value ? '' : newDateGroups[index].minter;
            newDateGroups[index].nft_id = '';
            newDateGroups[index].contract = '';
        }else if (key === 'nftCollectionOption') {
            // Update the NFT collection option for the specific date group
            newDateGroups[index].nftCollectionOption = value;
            const details = extractNftDetails(newDateGroups[index].nftUrl);
            newDateGroups[index].nft_id = details.nft_id || '';
            newDateGroups[index].contract = details.contract || '';
            newDateGroups[index].minter = details.minter || '';
            // Update attributes based on the selected option
            if (value === 'anyInCollection') {
                newDateGroups[index].nft_id = '';
                newDateGroups[index].minter = '';
            } else if (value === 'anyByMinter') {
                newDateGroups[index].contract = '';
                newDateGroups[index].nft_id = '';
            } else if (value === 'specific') {
                newDateGroups[index].minter = '';
            }
        }
        else {
            newDateGroups[index].nft_id = value ? '' : newDateGroups[index].nft_id;
            newDateGroups[index].minter = '';
        }
        newDateGroups[index][key] = value;
        setDateGroups(newDateGroups);
    }

    function handleSaveSchedule() {
        const scheduleArray = [];
        dateGroups.forEach(group => {
            const dateTime = `${group.date} ${group.time}`;
            const scheduleItem = {};
            scheduleItem[dateTime] = {
                minter: group.minter,
                nft_id: group.nft_id,
                contract: group.contract
            };
            scheduleArray.push(scheduleItem);
        });
        setAttributes({ schedule: JSON.stringify(scheduleArray) });
        setIsModalOpen(false);
    }
    

    const openModal = () => setIsModalOpen(true);
    const closeModal = () => setIsModalOpen(false);
    return (
        <div {...useBlockProps()}>
            <InspectorControls>
                
                <PanelBody title={__('Settings', 'fungate-block')}>
                <TextControl
            label={__('NFT URL', 'fungate-block')}
            value={nftUrl}
            onChange={(value) => handleNftUrlChange(value)}
            help={__('Enter the URL of the NFT on Etherscan.', 'fungate-block')}
        />
       <SelectControl
                        label={__('NFT Collection Option', 'fungate-block')}
                        value={nftCollectionOption}
                        options={[
                            { label: 'Specific NFT', value: 'specific' },
                            { label: 'Any in the collection', value: 'anyInCollection' },
                            { label: 'Any by this minter', value: 'anyByMinter' },
                        ]}
                        onChange={handleNftCollectionOptionChange}
                        help={__('Select how to apply the NFT gating.', 'fungate-block')}
                    />
                    <SelectControl
                        label={__('Chain', 'fungate-block')}
                        value={attributes.chain}
                        options={[
                            { label: 'Ethereum', value: 'ethereum' },
                            { label: 'Loopring', value: 'loopring' },
                            // Add other chains here
                        ]}
                        onChange={(chain) => setAttributes({ chain })}
                    />
                    <TextControl
                        label={__('Minter', 'fungate-block')}
                        value={attributes.minter}
                        onChange={(minter) => setAttributes({ minter })}
                    />
                    <TextControl
                        label={__('Contract', 'fungate-block')}
                        value={attributes.contract}
                        onChange={(contract) => setAttributes({ contract })}
                    />
                    <TextControl
                        label={__('NFT', 'fungate-block')}
                        value={attributes.nft_id}
                        onChange={(nft_id) => setAttributes({ nft_id })}
                    />
                    <TextControl
                        label={__('SCHEDULE', 'fungate-block')}
                        value={attributes.schedule}
                        onChange={(schedule) => setAttributes({ schedule })}
                    />
                    <Button onClick={openModal}>Open Token Gate Scheduler</Button>
                </PanelBody>
            </InspectorControls>

            {isModalOpen && (
    <Modal title="Token Gate Scheduler" onRequestClose={closeModal}>
        <div id="dateContainer">
            {dateGroups.map((group, index) => (
                <div className="fungate-dateGroup" key={index}>
                    <input type="date" value={group.date} onChange={(e) => handleDateGroupChange(index, 'date', e.target.value)} />
                    <input type="time" value={group.time} onChange={(e) => handleDateGroupChange(index, 'time', e.target.value)} />
                    <TextControl
                        label={__('NFT URL', 'fungate-block')}
                        value={group.nftUrl}
                        onChange={(value) => handleDateGroupChange(index, 'nftUrl', value)}
                        help={__('Enter the URL of the NFT on Etherscan.', 'fungate-block')}
                    />
                    <SelectControl
                                label={__('NFT Collection Option', 'fungate-block')}
                                value={group.nftCollectionOption}
                                options={[
                                    { label: 'Specific NFT', value: 'specific' },
                                    { label: 'Any in the collection', value: 'anyInCollection' },
                                    { label: 'Any by this minter', value: 'anyByMinter' },
                                ]}
                                onChange={(value) => handleDateGroupChange(index, 'nftCollectionOption', value)}
                                help={__('Select how to apply the NFT gating.', 'fungate-block')}
                            />
                    <textarea placeholder="Minter" value={group.minter} onChange={(e) => handleDateGroupChange(index, 'minter', e.target.value)} />
                    <textarea placeholder="NFT" value={group.nft_id} onChange={(e) => handleDateGroupChange(index, 'nft_id', e.target.value)} />
                    <textarea placeholder="Contract" value={group.contract} onChange={(e) => handleDateGroupChange(index, 'contract', e.target.value)} />
                    <button type="button" className="fungate-removeDate" onClick={() => handleRemoveDateGroup(index)}>Remove Rule Change</button>
                </div>
            ))}
            <Button onClick={handleAddDateGroup}>Add Rule Change</Button>
        </div>
        <Button className="fungate-saveButton" onClick={handleSaveSchedule}>Save Schedule</Button>
    </Modal>
)}

            <InnerBlocks />
        </div>
    );
}
