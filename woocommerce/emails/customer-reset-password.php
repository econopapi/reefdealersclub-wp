<?php
/**
 * Customer Reset Password email (RDC override).
 *
 * This template overrides WooCommerce's reset password email and adapts the
 * message/copy to Reef Dealers Club branding.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );
$reset_password_url         = add_query_arg(
	array(
		'key'   => $reset_key,
		'id'    => $user_id,
		'login' => rawurlencode( $user_login ),
	),
	wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) )
);
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php echo $email_improvements_enabled ? '<div class="email-introduction">' : ''; ?>
<?php /* translators: %s: Customer username */ ?>
<p><?php printf( esc_html__( 'Hola %s,', 'rdc-custom-astra' ), esc_html( $user_login ) ); ?></p>
<?php /* translators: %s: Store name */ ?>
<p><?php printf( esc_html__( 'Recibimos una solicitud para restablecer la contrasena de tu cuenta en %s.', 'rdc-custom-astra' ), esc_html( $blogname ) ); ?></p>
<?php if ( $email_improvements_enabled ) : ?>
	<div class="hr hr-top"></div>
	<?php /* translators: %s: Username */ ?>
	<p><?php echo wp_kses( sprintf( __( 'Usuario: <b>%s</b>', 'rdc-custom-astra' ), esc_html( $user_login ) ), array( 'b' => array() ) ); ?></p>
	<div class="hr hr-bottom"></div>
	<p><?php esc_html_e( 'Si no realizaste esta solicitud, puedes ignorar este correo de forma segura. Si deseas continuar, usa el siguiente boton para crear una nueva contrasena.', 'rdc-custom-astra' ); ?></p>
<?php else : ?>
	<?php /* translators: %s: Customer username */ ?>
	<p><?php printf( esc_html__( 'Usuario: %s', 'rdc-custom-astra' ), esc_html( $user_login ) ); ?></p>
	<p><?php esc_html_e( 'Si no realizaste esta solicitud, puedes ignorar este correo de forma segura. Si deseas continuar, usa el siguiente enlace:', 'rdc-custom-astra' ); ?></p>
<?php endif; ?>
<p>
	<a class="link" style="background-color:#009fe3;border-radius:4px;color:#ffffff;display:inline-block;font-weight:600;padding:12px 20px;text-decoration:none;" href="<?php echo esc_url( $reset_password_url ); ?>">
		<?php esc_html_e( 'Restablecer mi contrasena', 'rdc-custom-astra' ); ?>
	</a>
</p>
<p><?php esc_html_e( 'Gracias por confiar en Reef Dealers Club.', 'rdc-custom-astra' ); ?></p>
<p><?php esc_html_e( 'Equipo RDC', 'rdc-custom-astra' ); ?></p>
<?php echo $email_improvements_enabled ? '</div>' : ''; ?>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo $email_improvements_enabled ? '<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"><tr><td class="email-additional-content email-additional-content-aligned">' : '';
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
	echo $email_improvements_enabled ? '</td></tr></table>' : '';
}

do_action( 'woocommerce_email_footer', $email );
