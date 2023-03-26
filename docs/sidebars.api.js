/**
 * Creating a sidebar enables you to:
 - create an ordered group of docs
 - render a sidebar for each doc of that group
 - provide next/previous navigation

 The sidebars can be generated from the filesystem, or explicitly defined here.

 Create as many sidebars as you want.
 */

// DONT ASK WHY THIS IS REQUIRED ... I HAVE NO IDEA
let tmp = require("./docs-api/sidebar.js")
tmp = tmp.filter(s => s.label !== "UNTAGGED")
tmp = JSON.stringify(tmp).replaceAll('-api/', '')
tmp = JSON.parse(tmp)

const sidebars = {
  sidebar: tmp
};

module.exports = sidebars;
