<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/5
 * Time: 10:06
 */

namespace Home\Common;


use phpmailerException;

class MailModel
{

    protected $mailer;

    public function __construct()
    {
        vendor("phpmailer.class#phpmailer");
        vendor("PHPMailer.PHPMailerAutoload");
        $this->mailer = new \PHPMailer(true);
    }

    public function init()
    {
        $this->mailer->IsSMTP();
        $this->mailer->CharSet = 'UTF-8'; //设置邮件的字符编码，这很重要，不然中文乱码
        $this->mailer->SMTPAuth = true; //开启认证
        $this->mailer->Port = 25;

        $this->mailer->Host = C("MAIL_HOST");//邮件服务
        $this->mailer->Username = C("MAIL_USER");//发送邮箱
        $this->mailer->Password = C("MAIL_PASSWORD");//密码

        //$this->mailer->IsSendmail(); //如果没有sendmail组件就注释掉，否则出现“Could not execute: /var/qmail/bin/sendmail ”的错误提示
        $this->mailer->AddReplyTo(C("MAIL_USER"), C("MAIL_USER_NAME"));//回复地址
        $this->mailer->From = C("MAIL_USER");//发送邮箱
        $this->mailer->FromName = C("MAIL_USER_NAME");//邮箱帐号
    }

    /**
     * 邮件发送
     * $email 接收人
     * $title 邮件标题
     */
    public function send($email, $title, $body, $attachment=null)
    {
        $this->init();

        try {
            $this->mailer->AddAddress($email);//接收邮箱
            $this->mailer->Subject = $title;//邮件标题
            $this->mailer->Body = $body;//邮件正文
            $this->mailer->WordWrap = 80; // 设置每行字符串的长度

            $this->mailer->AddAttachment($attachment); //可以添加附件
            $this->mailer->IsHTML(true);
            $this->mailer->Send();
        } catch (phpmailerException $e) {
            throw new phpmailerException($e);
        }
        return null;
    }

    public function clearAddresses()
    {
        $this->mailer->clearAddresses();
    }

    public function clearAttachments()
    {
        $this->mailer->clearAttachments();
    }
}