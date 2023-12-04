<?php
add_action( 'show_user_profile', 'custom_user_profile_fields' );
add_action( 'edit_user_profile', 'custom_user_profile_fields' );

function custom_user_profile_fields( $user ) {
    // Retrieve the NFTs
    $nfts = get_user_meta( $user->ID, 'nfts', true );
    $ethereum_address = get_user_meta( $user->ID, 'ethereum_address', true );
    // Convert the NFTs array to a readable string
    $nfts_string = '';
    if ( is_array( $nfts ) ) {
        foreach ( $nfts as $nft ) {
            // Customize this line to format the NFTs however you like
            $nfts_string .= '<div style="border 2px solid black">'.print_r( $nft, true ) . '</div>';
        }
    }
    ?>
    <h3>Extra profile information</h3>

    <table class="form-table">
        <tr>
            <th><label for="nfts">Ethereum Address</label></th>
            <td>
                <?php echo $ethereum_address; ?>
            </td>
            <th><label for="nfts">NFTs</label></th>
            <td>
                <?php echo $nfts_string; ?>
            </td>
        </tr>
    </table>
    <?php
}