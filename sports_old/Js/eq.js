function advancedSearch() {
  const searchTerm = document.getElementById("search_term").value;
  const district = document.getElementById("dist").value;
  const division = document.getElementById("divi").value;

  document.getElementById("data-table1").innerHTML =
    "<tr><td colspan='4'>Loading...</td></tr>";

  const xhr = new XMLHttpRequest();
  const params = new URLSearchParams({
    search_term: searchTerm,
    dist: district,
    divi: division,
  }).toString();

  xhr.open("GET", `php/filter_eq.php?${params}`, true);
  xhr.onload = function () {
    if (xhr.status === 200) {
      const response = xhr.responseText.trim();

      document.getElementById("data-table1").innerHTML =
        response || "<tr><td colspan='4'>No records found</td></tr>";
      clearFilterFields();
    } else {
      console.error("Error: " + xhr.statusText);
      document.getElementById("data-table1").innerHTML =
        "<tr><td colspan='4'>An error occurred</td></tr>";
    }
  };
  xhr.onerror = function () {
    console.error("Request failed");
    document.getElementById("data-table1").innerHTML =
      "<tr><td colspan='4'>An error occurred</td></tr>";
  };
  xhr.send();
}

function clearFilterFields() {
  document.getElementById("search_term").value = "";
  document.getElementById("dist").value = "";
  document.getElementById("divi").value = "";
}
