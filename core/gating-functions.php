<?php
defined('ABSPATH') or die('No script kiddies please!');
/**
 * Checks if a user has certain NFTs based on their attributes.
 * 
 * @param string $selected_account The account to check for NFT ownership.
 * @param array $attributes The attributes of the NFTs to check for.
 * @return bool True if the user has the NFTs matching the attributes, false otherwise.
 */
function fungate_has_membership($selected_account, $attributes) {
    if (empty($selected_account)) {
        return false;
    }
    // Check if all attributes are empty or not set
    $all_attributes_empty = true;
    foreach ($attributes as $attr_value) {
        if ($attr_value !== "") {
            $all_attributes_empty = false;
            break;
        }
    }

    // If all attributes are empty, do not lock the gate
    if ($all_attributes_empty) {
        return true;
    }
    // Retrieve the NFTs for the current user
    $user_has = array();
    if (get_current_user_id()) {

        // Retrieve different types of NFTs and check each against the specified attributes
        $nft_types = array(
            'nfts' => get_user_meta(get_current_user_id(), 'nfts', true),
            'eth_nfts' => (get_option('eth_enabled')) ? get_user_meta(get_current_user_id(), 'eth_nfts', true) : array(),
            'arbitrum_nfts' => (get_option('arbitrum_enabled')) ? get_user_meta(get_current_user_id(), 'arbitrum_nfts', true) : array(),
            'optimism_nfts' => (get_option('optimism_enabled')) ? get_user_meta(get_current_user_id(), 'optimism_nfts', true) : array(),
            'starknet_nfts' => (get_option('starknet_enabled')) ? get_user_meta(get_current_user_id(), 'starknet_nfts', true) : array(),
            'polygon_nfts' => (get_option('polygon_enabled')) ? get_user_meta(get_current_user_id(), 'polygon_nfts', true) : array()
        );

        foreach ($nft_types as $nfts) {
            if ($nfts) {
                foreach ($nfts as $nft) {
                    if (fungate_check_conditions($nft, $attributes)) {
                        $user_has[] = $nft;
                    }
                }
            }
        }
    }

    return !empty($user_has);
}

/**
 * Checks if an NFT meets certain conditions.
 * 
 * @param array $nft The NFT data to check.
 * @param array $attributes The conditions to check against.
 * @return bool True if the NFT meets the conditions, false otherwise.
 */
function fungate_check_conditions($nft, $attributes) {
    // Return true if no conditions are set
    if (empty(array_filter($attributes))) {
        return true;
    }

    // Check each attribute against the NFT data
    foreach ($attributes as $attr => $expression) {
        $data = $nft[$attr] ?? null;
        if ($expression !== '' && $data !== null) {
            if (!fungate_evaluate_logical_expression($expression, $data)) {
                return false;
            }
        }
    }

    return true;
}

/**
 * Evaluates a logical expression against given data.
 * 
 * @param string $expressions The logical expression to evaluate.
 * @param string $data The data to compare against.
 * @return bool The result of the evaluation.
 */
function fungate_evaluate_logical_expression($expressions, $data) {
	// Handle not operator (!)
	if (substr($expressions, 0, 1) === '!') {
		$expressions = substr($expressions, 1);  // remove the '!'
		$result = !fungate_evaluate_logical_expression($expressions, $data);
		return $result;
	}
    
    // Handle parentheses
    while (($openPos = strrpos($expressions, '(')) !== false) {
        $closePos = strpos($expressions, ')', $openPos);
        if ($closePos === false) {
            throw new Exception('Invalid expression: missing closing parenthesis');
        }

        // Evaluate innermost sub-expression
        $subExpr = substr($expressions, $openPos + 1, $closePos - $openPos - 1);
        $result = fungate_evaluate_logical_expression($subExpr, $data);

        // Replace sub-expression with result
        $expressions = substr_replace($expressions, $result ? 'true' : 'false', $openPos, $closePos - $openPos + 1);
    }

    // Evaluate logical operators (top level only)
    if (strpos($expressions, ',') !== false) {
        foreach (explode(',', $expressions) as $expr) {
            if (fungate_evaluate_logical_expression($expr, $data)) {
                return true;
            }
        }
        return false;
    } else if (strpos($expressions, '+') !== false) {
        foreach (explode('+', $expressions) as $expr) {
            if (!fungate_evaluate_logical_expression($expr, $data)) {
                return false;
            }
        }
        return true;
    } else if (strpos($expressions, '*') !== false) {
        $tokens = explode('*', $expressions);
        return substr_count($data, $tokens[0]) >= (int)$tokens[1];
    } else {
        return $expressions === 'true' ? true : ($expressions === $data);
    }
}