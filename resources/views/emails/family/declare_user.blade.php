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
                              <?php if ($type == "primary_sender") {?>
                              <tr>
                                  <td><table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                      <tbody>
                                      <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top; padding:10px 0;"><strong> Hi <?php echo $data->senderDetail['name']; ?>,</strong></td>
                                        </tr>
                                      <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:16px; line-height:22px; vertical-align: top; padding:10px 0;"> Primary user <strong><?php echo $data->receiverDetail['name']; ?></strong> has declared you as dead.</td>
                                        </tr>
                                    </tbody>
                                    </table>
                                    </td>
                                </tr>
                              <?php } ?>
                                <?php if($type == "primary_receiver"){?>
                                    <tr>
                                        <td><table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                            <tbody>
                                            <tr>
                                                <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top; padding:10px 0;"><strong> Hi <?php echo $data->receiverDetail['name']; ?>,</strong></td>
                                              </tr>
                                            <tr>
                                                <td style="font-family: Open Sans,open-sans,sans-serif; font-size:16px; line-height:22px; vertical-align: top; padding:10px 0;"> Primary user has declared <strong><?php echo $data->senderDetail['name']; ?></strong> as dead.</td>
                                              </tr>
                                          </tbody>
                                          </table>
                                          </td>
                                      </tr>
                              <?php }?>

                              <?php if ($type == "guarantee_sender") {?>
                                <tr>
                                    <td><table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                        <tbody>
                                        <tr>
                                            <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top; padding:10px 0;"><strong> Hi <?php echo $data->senderDetail['name']; ?>,</strong></td>
                                          </tr>
                                        <tr>
                                            <td style="font-family: Open Sans,open-sans,sans-serif; font-size:16px; line-height:22px; vertical-align: top; padding:10px 0;"> Guarantee user <strong><?php echo $data->receiverDetail['name']; ?></strong> has declared you as dead.</td>
                                          </tr>
                                      </tbody>
                                      </table>
                                      </td>
                                  </tr>
                                <?php } ?>
                                  <?php if($type == "guarantee_receiver"){?>
                                      <tr>
                                          <td><table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                              <tbody>
                                              <tr>
                                                  <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top; padding:10px 0;"><strong> Hi <?php echo $data->receiverDetail['name']; ?>,</strong></td>
                                                </tr>
                                              <tr>
                                                  <td style="font-family: Open Sans,open-sans,sans-serif; font-size:16px; line-height:22px; vertical-align: top; padding:10px 0;"> Guarantee user has declared <strong><?php echo $data->senderDetail['name']; ?></strong> as dead.</td>
                                                </tr>
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
