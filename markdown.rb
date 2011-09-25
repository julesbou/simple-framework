#!/usr/bin/ruby
#  Github-flavored markdown to HTML, in a command-line util.
#
#  $ cat README.md | ./ghmarkdown.rb
#
#  Notes:
#
#  You will need to install Pygments for syntax coloring
#
#    $ sudo easy_install pygments
#
#  Install the gems redcarpet, albino, and nokogiri
#
#  To work with http://markedapp.com/ I also had to
#    $ sudo ln -s /usr/local/bin/pygmentize /usr/bin
#
require 'rubygems'
require 'redcarpet'
require 'albino'
require 'nokogiri'

def markdown(text)
    options = [:hard_wrap, :filter_html, :autolink, :no_intraemphasis, :fenced_code, :gh_blockcode]
    html = Redcarpet.new(text, *options).to_html 
    syntax_highlighter(html)
end

def syntax_highlighter(html)
    doc = Nokogiri::HTML(html)
    doc.search("//pre[@lang]").each do |pre|
        pre.replace Albino.colorize(pre.text.rstrip, pre[:lang])
    end
    doc.at_css("body").inner_html.to_s
end

Dir.foreach('docs/markdown') do |item|
    next if item == '.' or item == '..'
    newItem = item[0, item.length - 3] + ".html"
    content = File.read("docs/html/_layout.html");
    content[content.index("%CONTENT%"), "%CONTENT%".length] = markdown(File.read("docs/markdown/" + item))
    File.new("docs/html/" + newItem, "w+").puts(content)
end
