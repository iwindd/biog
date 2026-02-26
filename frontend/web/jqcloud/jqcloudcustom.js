var word_array = [
    {text: "ทดสอบ", weight: 15},
    {text: "Ecosystem", weight: 9},
    {text: "Research", weight: 1},
    {text: "Analysis", weight: 7},
    {text: "Cell", weight: 5},
    {text: "Science", weight: 5},
    {text: "Energy", weight: 5}
];

$(function() {
  $("#world-cloud").jQCloud(word_array);
});