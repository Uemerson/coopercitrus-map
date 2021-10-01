<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous" />

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <link rel="stylesheet" type="text/css" href="./style.css" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />

    <!-- AJAX e JQuery -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>

    <title>Maps</title>
</head>

<body>
    <div class="parent">
        <div class="row px-5">
            <div class="col-sm-5">
                <div class="col-sm-12 mb-4">
                    <div class="there-we-are">ONDE ESTAMOS</div>
                </div>
                <div class="col-sm-12 mb-1">
                    <i class="fa fa-map-marker" style="color: #37945a; font-size: 18px; margin-right: 5px"></i>
                    <span class="find-branch">Encontre a filial mais próxima de você</span>
                </div>
                <div class="col-sm-12 mb-4">
                    <div class="input-group">
                        <input class="form-control py-2 border-right-0 border input" type="search"
                            placeholder="São Paulo  |" id="search-input" />
                        <span class="input-group-append" style="background: #efe9e5">
                            <div class="input-group-text bg-transparent">
                                <i class="fa fa-search" style="color: #37945a"></i>
                            </div>
                        </span>
                    </div>
                </div>

                <div class="col-sm-12 mb-4 results">RESULTADOS</div>

                <div class="col-sm-12">
                    <hr />
                </div>

                <!-- PHP -->
                <script>
                let map = null;
                let marker = null;
                let markers = [];
                let InforObj = [];

                async function initMap() {
                    map = new google.maps.Map(document.getElementById('map'), {
                        center: {
                            lat: -22.5481643,
                            lng: -50.8804391
                        },
                        zoom: 6,
                    });

                    await fetchData('');
                }

                function closeOtherInfo() {
                    if (InforObj.length > 0) {
                        /* detach the info-window from the marker ... undocumented in the API docs */
                        InforObj[0].set("marker", null);
                        /* and close it */
                        InforObj[0].close();
                        /* blank the array */
                        InforObj.length = 0;
                    }
                }

                const contentString = (item) => {
                    return `<div id="content">
                                <div 
                                    style=
                                    "color: #1f5433; 
                                    font-weight: bold;
                                    font-size: 14px;
                                    margin-bottom: 5px;">
                                    COOPERCITRUS - ${item.city.toUpperCase()}
                                </div>
                                <div 
                                    style=
                                    "color: #7a7a7a;
                                    font-size: 14px;
                                    margin-bottom: 5px;">
                                    ${item.address}
                                </div>
                                <div
                                    style=
                                    "color: #82B192; 
                                    font-weight: bold;
                                    font-size: 14px;
                                    margin-bottom: 5px;">
                                    ${item.phone}
                                </div>
                                <div 
                                    style=
                                    "color: #7a7a7a;
                                    font-size: 14px;">
                                    ${item.email}
                                </div>
                            </div>`
                }

                const addMarker = (item) => {
                    const svgMarker = {
                        path: "M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z",
                        fillColor: "#EA4335",
                        fillOpacity: 1,
                        strokeWeight: 0,
                        scale: 0.07,
                        anchor: new google.maps.Point(200, 550),
                    };

                    const marker = new google.maps.Marker({
                        position: {
                            lat: Number(item.lat),
                            lng: Number(item.lng)
                        },
                        map,
                        optimized: true,
                        icon: svgMarker,
                    })

                    const infowindow = new google.maps.InfoWindow({
                        content: contentString(item),
                    });

                    marker.addListener("click", () => {
                        closeOtherInfo();
                        infowindow.open({
                            anchor: marker,
                            map,
                            shouldFocus: false,
                        });
                        InforObj[0] = infowindow;
                    });

                    markers.push(marker);
                }

                function setMapOnAll(map) {
                    for (let i = 0; i < markers.length; i++) {
                        markers[i].setMap(map);
                    }

                    if (marker)
                        marker.setMap(null)
                }

                const fetchData = async (filter) => {
                    let data = new FormData();
                    data.append("filter", filter)

                    const response = await fetch('filter.php', {
                        method: "POST",
                        body: data
                    })

                    const elements = await response.json();

                    const items = document.getElementById("items");
                    items.innerHTML = "";

                    // reset google maps state
                    map.setCenter({
                        lat: -22.5481643,
                        lng: -50.8804391
                    })
                    map.setZoom(6)
                    setMapOnAll(null)

                    elements.map(item => {
                        let element = document.createElement('div');
                        element.setAttribute('id', item.id);

                        const title = document.createTextNode(`COOPERCITRUS - ${item.city}`
                            .toUpperCase())
                        const address = document.createTextNode(
                            item.address)
                        const phone = document.createTextNode(item.phone)
                        const mail = document.createTextNode(
                            item.email)

                        element.className = "col-sm-12 mb-4 card"
                        element.appendChild(title)
                        element.appendChild(document.createElement("br"));
                        element.appendChild(address)
                        element.appendChild(document.createElement("br"));
                        element.appendChild(phone)
                        element.appendChild(document.createElement("br"));
                        element.appendChild(mail)
                        element.appendChild(document.createElement("br"));

                        element.addEventListener('click', function(e) {
                            elements.map(item => {
                                document.getElementById(item.id)
                                    .setAttribute(
                                        "class",
                                        "col-sm-12 mb-4 card")
                            })

                            document.getElementById(item.id).setAttribute(
                                "class",
                                "col-sm-12 mb-4 card selected");

                            const svgMarker = {
                                path: "M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z",
                                fillColor: "#1f5433",
                                fillOpacity: 1,
                                strokeWeight: 0,
                                scale: 0.07,
                                anchor: new google.maps.Point(200, 550),
                            };

                            if (marker)
                                marker.setMap(null)

                            marker = new google.maps.Marker({
                                position: {
                                    lat: Number(item.lat),
                                    lng: Number(item.lng)
                                },
                                map,
                                optimized: true,
                                icon: svgMarker,
                            });

                            const infowindow = new google.maps.InfoWindow({
                                content: contentString(item)
                            });

                            // auto open info
                            closeOtherInfo();
                            infowindow.open({
                                anchor: marker,
                                map,
                                shouldFocus: false,
                            });
                            InforObj[0] = infowindow;

                            marker.addListener("click", () => {
                                closeOtherInfo();
                                infowindow.open({
                                    anchor: marker,
                                    map,
                                    shouldFocus: false,
                                });
                                InforObj[0] = infowindow;
                            });

                            map.panTo({
                                lat: Number(item.lat),
                                lng: Number(item.lng)
                            });
                            map.setZoom(8);
                        });

                        items.appendChild(element);

                        addMarker(item);
                    });
                }

                const onChange = async (evt) => {
                    await fetchData(evt.target.value)
                }

                const input = document.getElementById('search-input');
                input.addEventListener('input',
                    onChange, false);
                </script>

                <div class="col-sm-12 scroll" id="items">
                </div>
            </div>

            <div class="col-sm-7">
                <div id="map"></div>
            </div>
        </div>
    </div>

    <!-- Google maps -->
    <!-- Async script executes immediately and must be after any DOM elements used in callback. -->
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE&callback=initMap&v=weekly" async></script>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
    </script>
</body>

</html>