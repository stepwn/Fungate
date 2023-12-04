<?php

if ( wp_is_block_theme() ) {
    block_header_area();
}
?>

<style>
    .fungate-wrapper {
    display: flex;
    justify-content: center; /* Center the columns horizontally */
    width: 100%;
}

<?php if ( get_post_meta( get_the_ID(), 'fungate_layout_style', true ) === 'two-column' ) : ?>
.fungate-two-column-layout {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: stretch;
    max-width: 100%; /* Set a maximum width for the columns */
    margin: auto; /* Center the columns within the wrapper */
    padding: 20px; /* Add padding on either side */
}

.fungate-left-column,
.fungate-right-column {
    flex: 0 0 calc(50% - 10px); /* Each column takes up 50% width with spacing */
    text-align: center;
}

.fungate-spacing-element {
    flex: 0 0 20px; /* Adjust this value to control spacing width */
}

.fungate-right-column {
    display: flex;
    flex-direction: column;
    justify-content: center;
}
<?php else : ?>
.fungate-stacked-layout {
    text-align: center;
    margin:auto;
    padding: 20px;
    width: 100%;
}
<?php endif; ?>

    /* Optional styling for better appearance */
    img {
        height: auto;
        max-width: 100%;
        border-radius: 16px;
    }

    @media (max-width: 768px) {
        .fungate-two-column-layout {
            flex-direction: column;
            align-items: center;
        }

        .fungate-left-column {
            flex: 1;
            margin-right: 0;
            margin-bottom: 20px;
        }
        .fungate-wrapper{
            width:100%;
        }
        .fungate-stacked-layout{
            width:100%;
        }
        img{
            max-width:100%;
            height:auto;
        }
    }
</style>

<?php
while ( have_posts() ) : the_post();
?>
<div class="fungate-wrapper">
    <?php if ( get_post_meta( get_the_ID(), 'fungate_layout_style', true ) === 'two-column' ) : ?>
    <div class="fungate-two-column-layout">
        <div class="fungate-left-column">
            <h1 class="fungate-nft-title"><?php the_title(); ?></h1>
            <?php the_post_thumbnail( 'large' ); ?>
            <p><?php echo get_post_meta( get_the_ID(), 'fungate_short_description', true ); ?></p>
        </div>
        <div class="fungate-right-column">
            <?php the_content(); ?>
        </div>
    </div>
    <?php else : ?>
    <div class="fungate-stacked-layout">
        <h1 class="fungate-nft-title"><?php the_title(); ?></h1>
        <?php the_post_thumbnail( 'large' ); ?>
        <p><?php echo get_post_meta( get_the_ID(), 'fungate_short_description', true ); ?></p>
        <?php the_content(); ?>
    </div>
    <?php endif; ?>
</div>
<?php
endwhile;

if ( wp_is_block_theme() ) {
    block_footer_area();
}
?>
