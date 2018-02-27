//
//  ViewController.swift
//  juice
//
//  Created by Jack Crawford on 2/18/18.
//  Copyright © 2018 tangle. All rights reserved.
//

import UIKit

class ViewController: UIViewController {
    @IBOutlet weak var webView: UIWebView!
    
    override func viewDidLoad() {
        super.viewDidLoad()
        // Do any additional setup after loading the view, typically from a nib.
        let url = URL(string:"http://ec2-52-24-190-88.us-west-2.compute.amazonaws.com/tangle/juice.html")
        let request = NSURLRequest(url: url!)
        webView.loadRequest(request as URLRequest)
    }

    override func didReceiveMemoryWarning() {
        super.didReceiveMemoryWarning()
        // Dispose of any resources that can be recreated.
    }


}

