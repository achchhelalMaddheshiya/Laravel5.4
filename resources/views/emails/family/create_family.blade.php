@include('includes.email_header');
<table width="100%" cellpadding="0" cellspacing="0" border="0" id="backgroundTable">
    <tbody>
        <tr>
          <td><table width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
              <tbody>
              <tr>
                  <td width="100%">
                  <table bgcolor="#ffffff" width="600" cellpadding="0" cellspacing="0" border="0" align="center" class="devicewidth">
                      <tbody>
                      <tr>
                          <td width="100%" height="20"></td>
                        </tr>
                      <tr>
                          <td width="100%"><table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                              <tbody>
                              <?php if ($family->user_id && !empty($family->user_id) && $family->user_id != null) {?>
                              <tr>
                                  <td><table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                      <tbody>
                                      <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top; padding:10px 0;"><strong> Hi <?php echo $family->receiverDetail['name']; ?>,</strong></td>
                                        </tr>
                                      <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:16px; line-height:22px; vertical-align: top; padding:10px 0;"> <?php echo $family->senderDetail['name']; ?> send you a request to add you as a <?php echo $family->familyTypeDetail['slug']; ?> in Willodiary account.</td>
                                        </tr>
                                        <?php if ($family->familyTypeDetail['slug'] != 'member') {?>
                                      <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top;">Your Mutual code for future access is <?php echo $family->code; ?></td>
                                        </tr>
                                        <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top;">Please do not share this code with anyone.</td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                    </table>
                                    </td>
                                </tr>
                              <?php } else {?>
                                <tr>
                                  <td><table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                      <tbody>
                                      <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top; padding:10px 0;"><strong> Hi User,</strong></td>
                                        </tr>
                                      <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:16px; line-height:22px; vertical-align: top; padding:10px 0;"> <?php echo $family->senderDetail['name']; ?> send you a request to add you as a <?php echo $family->familyTypeDetail['slug']; ?> in Willodiary account.</td>
                                        </tr>
                                        <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:16px; line-height:22px; vertical-align: top; padding:10px 0;"> Signup in Willodiary and enjoy the experience. </td>
                                        </tr>
                                        <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:16px; line-height:22px; vertical-align: top; padding:10px 0;">For Signup, <a href="{{ config('variable.FRONTEND_URL') }}" style="color: #90cbf5;">
                                                            <span style="color: #90cbf5;">Click Here</span>
                                                        </a>. </td>
                                        </tr>
                                        <?php if ($family->familyTypeDetail['slug'] != 'member') {?>
                                      <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top;">Your Mutual code for future access is <?php echo $family->code; ?></td>
                                        </tr>
                                        <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top;">Please do not share this code with anyone.</td>
                                        </tr>
                                        <?php }?>
                                    </tbody>
                                    </table>
                                    </td>
                                </tr>
                              <?php }?>
                              <tr>
                                  <td><table width="560" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                      <tbody>
                                      <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top; padding:10px 0; "> Kind regards, </td>
                                        </tr>
                                      <tr>
                                          <td> Willodiary Team </td>
                                        </tr>

                                    </tbody>
                                    </table></td>
                                </tr>

                              <tr>
                                  <td style="border-bottom:2px  #ccc; height:5px; padding:10px;"></td>
                                </tr>
                            </tbody>
                            </table></td>
                        </tr>
                    </tbody>
                    </table>

                 </td>
               </tr>
          </td>
        </tr>
    </tbody>
</table>


<table  width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
       <td>
           <tr>
            <table width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#312055" align="center">
                <tr>
                   <td height="20px"></td>
                </tr>
            </table>


           </tr>
       </td>
    </tr>
</table>
@include('includes.email_footer')