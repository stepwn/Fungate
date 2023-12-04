<?php

function has_membership($selected_account, $attributes) {
    if (empty($selected_account)) {
        return false;
    }
    // Retrieve the NFTs
    if(get_current_user_id()){
        if(!get_user_meta(get_current_user_id(), 'ethereum_address', true)){
            return false;
        }
        $nfts = get_user_meta( get_current_user_id(), 'nfts', true );
        if($nfts){
            foreach ($nfts as $nft) {
                $nft_conditions = check_conditions($nft, $attributes);
                if ($nft_conditions) {
                    $user_has[] = $nft;
                }
            }
        }
    }
    return !empty($user_has);
}

function check_conditions($nft, $attributes) {
    // If no conditions are set, return false.
    $attributes_filled = array_filter($attributes);
    if(empty($attributes_filled)){
        return false;
    }
    foreach ($attributes as $attr => $expression) {
		switch ($attr) {
			case 'token':
				$data = $nft['tokenAddress'];
				break;
            case 'contract':
                $data = $nft['tokenAddress'];
                break;
			case 'nft_id':
				$data = $nft['nftId'];
				break;
            case 'nft':
                $data = $nft['nftId'];
                break;
			case 'minter':
				$data = $nft['minter'];
				break;
		}

        if ($expression !== '') {
            if (!evaluate_logical_expression($expression, $data)) {
                return false;
            }
        }
    }

    return true;
}

function evaluate_logical_expression($expressions, $data) {
	// Handle not operator (!)
	if (substr($expressions, 0, 1) === '!') {
		$expressions = substr($expressions, 1);  // remove the '!'
		$result = !evaluate_logical_expression($expressions, $data);
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
        $result = evaluate_logical_expression($subExpr, $data);

        // Replace sub-expression with result
        $expressions = substr_replace($expressions, $result ? 'true' : 'false', $openPos, $closePos - $openPos + 1);
    }

    // Evaluate logical operators (top level only)
    if (strpos($expressions, ',') !== false) {
        foreach (explode(',', $expressions) as $expr) {
            if (evaluate_logical_expression($expr, $data)) {
                return true;
            }
        }
        return false;
    } else if (strpos($expressions, '+') !== false) {
        foreach (explode('+', $expressions) as $expr) {
            if (!evaluate_logical_expression($expr, $data)) {
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