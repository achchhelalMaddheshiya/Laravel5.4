@include('includes.email_header')
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
                              <tr>
                                  <td><table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                      <tbody>
                                      <tr>
                                          <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top; padding:10px 0;"><strong> Dear <?php echo ($data['MailToUser'] == '') ? ' Admin' : ucfirst($data['name']); ?>,</strong></td>
                                        </tr>
                                    
                                    </tbody>
                                    </table></td>
                                </tr>
                                <?php if ($data['MailToUser'] != '') { ?>
                                    <tr>
                                        <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top;">Thank you for contacting Willodiary Team. We will get in touch shortly.</td>
                                      </tr>

                                      <tr>
                                        <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top;">You have submitted below mentioned information</td>
                                        </tr>

                                <?php } else {?>
                                    <tr>
                                            <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top;">A user has contacted you with below mentioned information, reach him as soon as possible</td>
                                            </tr>
                                    <?php }?>

                              <tr>
                                  <td><table width="560" align="center" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                      <tbody>
                                        <tr>
                                            <td width="100%" height="10" style="border-bottom:1px solid #eee"></td>
                                          </tr>

                                          <tr>
                                            <td width="100%" height="10">Name: </td>
                                            <td width="100%" height="10"><?php echo !empty($data['name']) ? $data['name'] : ''; ?></td>
                                          </tr>

                                          <tr>
                                            <td width="100%" height="10">Subject: </td>
                                            <td width="100%" height="10"><?php echo !empty($data['subject']) ? $data['subject'] : ''; ?></td>
                                          </tr>


                                        <tr>
                                          <td width="100%" height="10">Email: </td>
                                          <td width="100%" height="10"><?php echo !empty($data['email']) ? $data['email'] : ''; ?></td>
                                        </tr>


                                        <tr>
                                            <td width="100%" height="10" >Message: </td>
                                            <td width="100%" height="10"><?php echo !empty($data['message']) ? $data['message'] : ''; ?></td>
                                         </tr>

                                      
                                     
                                      <tr>
                                          <td width="100%" height="10"></td>
                                        </tr>
                                    </tbody>
                                    </table></td>
                                </tr>
                               
                             
                              <tr>
                                  <td><table width="560" cellpadding="0" cellspacing="0" border="0" class="devicewidthinner">
                                      <tbody>
                                            <tr>
                                            <td style="font-family: Open Sans,open-sans,sans-serif; font-size:14px; line-height:22px; vertical-align: top; padding:10px 0; "> Kind regards</td>
                                            </tr>

                                      <tr>
                                          <td>   Willodiary Team  </td>
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
