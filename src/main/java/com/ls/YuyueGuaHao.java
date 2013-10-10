package com.ls;

import java.io.IOException;

import org.apache.commons.httpclient.HttpClient;
import org.apache.commons.httpclient.HttpException;
import org.apache.commons.httpclient.HttpMethod;
import org.apache.commons.httpclient.methods.GetMethod;
import org.apache.commons.httpclient.params.HttpMethodParams;


public class YuyueGuaHao {

	/**
	 * @param args
	 */
	public static void main(String[] args) {
		String CHANKEId = "1442_1"; //产科的id
		String testId = "1572_1"; //测试科室id
		String keid = testId;
		String detectURLStart = "http://www.bjguahao.gov.cn/comm/ghao.php?hpid=104&keid=" + keid + "&date1=2013-10-";
		String httpPageResultString = null;
		String detectURL = null;
        int i = 14; // i = 14 -> 18
		do {
			if (i > 18) i = 14;
			detectURL = detectURLStart + i;
        	httpPageResultString = getGetHtml(detectURL, "UTF-8");
        	if (! httpPageResultString.contains("开始预约"))  { //预约信息页面失效
        	    System.out.println(new java.util.Date() + " detect url: " + detectURL);
        	    javax.swing.JOptionPane.showInputDialog("cookie过期了，重新登录修正后再来过");
            } else { //正常打开预约信息页面
        	    if (httpPageResultString.contains("预约挂号</a>")) {
            	    System.out.println("有号了：" + new java.util.Date() + " detect url: " + detectURL);
        	        javax.swing.JOptionPane.showInputDialog("现在有号");
                } else {
                	System.out.println("还没号：" + new java.util.Date() + " detect url: " + detectURL);
                }
        	}
            i++;
		    try {
				Thread.sleep(5000);
			} catch (InterruptedException e) {
				e.printStackTrace();
			}
		} while (true); 
	}
	
	
	public static String getGetHtml(String url,String encode){
		HttpMethod method = new GetMethod(url);
		if(null != encode)
			method.getParams().setParameter(HttpMethodParams.HTTP_CONTENT_CHARSET, encode);
		try {
			HttpClient httpClient = new HttpClient();
			method.addRequestHeader("Referer", "http://www.bjguahao.gov.cn/comm/yyks.php?hpid=104");
			method.addRequestHeader("Cookie", "xyz"); // need change your cookie
			httpClient.executeMethod(method);
			String html = method.getResponseBodyAsString();
			return html;
		} catch (HttpException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		} finally {
			method.releaseConnection();
		}
		return null;
	}

}

