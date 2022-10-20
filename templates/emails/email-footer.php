<?php
/**
 * MVX Email Footer
 *
 * Override this template by copying it to yourtheme/MultiVendorX/emails/email-footer.php
 *
 * @author 		MultiVendorX
 * @package MultiVendorX/Templates
 * @version   	2.7.3
 */
 
global $MVX;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
?>
															</div>
														</td>
													</tr>
												</table>
												<!-- End Content -->
											</td>
										</tr>
									</table>
									<!-- End Body -->
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<!-- Footer -->
									<table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
										<tr>
											<td valign="top">
												<table border="0" cellpadding="10" cellspacing="0" width="100%">
													<tr>
														<td colspan="2" valign="middle" id="credit">
															<?php echo wpautop( wp_kses_post( wptexturize( apply_filters( 'mvx_email_footer_text', get_option( 'mvx_email_footer_text' ) ) ) ) ); ?>
														</td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
									<!-- End Footer -->
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>
