window.addEventListener('DOMContentLoaded', () => {
    let wordCloudButton = document.getElementsByClassName('word-cloud')[0]
    let wordCloudSummary = $('#word-cloud-summary')

    let keywordMapButton = document.getElementsByClassName('keyword-map')[0]
    let keywordMapSummary = document.getElementById('keyword-map-summary')

    let wordCloudAPI = '/api/word-cloud/summary'
    let keywordMapAPI = '/api/keyword-map/summary'

    let fectAPI = async (url) => {
        const response = await fetch(url)
            .then((response) => {
                if (response.status >= 200 && response.status < 300) {
                    return Promise.resolve(response)
                }

                return Promise.reject(new Error(response.statusText))
            })
            .catch((error) => {
                return null
            })

        return response.json()
    }

    let setupJQCloud = (words) => {
        wordCloudSummary.removeClass('d-none')
        keywordMapSummary.classList.add('d-none')

        wordCloudSummary.jQCloud(words, {
            classPattern: null,
            colors: ['#218838', '#008B65', '#007E55', '#008C8F', '#0089AF', '#0082C0', '#4679BF'],
            delay: 1,
            autoResize: true,
            fontSize: {
                from: 0.1,
                to: 0.02,
            },
            // shape: 'rectangular',
        })
    }

    let setupWordCloudDescription = (words) => {
        words.sort((preValue, nextValue) => (preValue.weight > nextValue.weight ? 1 : -1))

        let tableRowhtml = ''

        for (let index = 0; index < 10; index++) {
            leftWord = words[index].text ? words[index].text : ''
            leftLink = words[index].link ? words[index].link : '#'

            rightWord = words[index + 1].text ? words[index + 1].text : ''
            rightLink = words[index + 1].link ? words[index + 1].link : '#'

            tableRowhtml += `
                <tr>
                    <td><a href="${leftLink}" >${leftWord}</a></td>
                    <td><a href="${rightLink}" >${rightWord}</a></td>

            `

            if (index % 2 != 0 || index == 0 || index == 10) {
                tableRowhtml += '</tr>'
            }

            index++
        }

        document.getElementById('word-cloud-description').innerHTML = tableRowhtml
    }

    let setupKeywordMap = (response) => {
        wordCloudSummary.addClass('d-none')
        keywordMapSummary.classList.remove('d-none')

        response = JSON.parse(response)

        nodes = new vis.DataSet(response.nodes)
        edges = new vis.DataSet(response.edges)

        let container = keywordMapSummary
        let data = {
            nodes: nodes,
            edges: edges,
        }
        let options = {
            nodes: {
                borderWidth: 1,
                size: 30,
                shadow: true,
                color: {
                    border: '#40c379',
                    background: '#40c379',
                },
                font: {
                    color: '#000000',
                    size: 16, // px
                    face: '"Kanit", sans-serif',
                },
            },
            edges: {
                color: {
                    inherit: 'from',
                },
                // smooth: {
                //     type: 'cubicBezier'
                // }
            },
            physics: true,
            interaction: {
                hideEdgesOnDrag: true,
                hideEdgesOnZoom: false,
            },
        }
        let network = new vis.Network(container, data, options)

        network.on('doubleClick', (params) => {
            if (params.nodes.length > 0) {
                let nodeId = params.nodes[0]

                let searchType = 'taxonomy'
                let contentType = ['type_1', 'type_2', 'type_3', 'type_4', 'type_5', 'type_6']

                if (contentType.includes(nodeId)) {
                    searchType = 'content_type'
                }

                window.open('/search?' + searchType + '=' + nodes.get(nodeId).label, '_blank')
            }
        })
    }

    let fetchWordCloud = () => {
        fectAPI(wordCloudAPI)
            .then((response) => {
                setupJQCloud(response)
                setupWordCloudDescription(response)
            })
            .catch((error) => console.log(error))
    }

    let fetchKeywordMap = () => {
        fectAPI(keywordMapAPI)
            .then((response) => {
                setupKeywordMap(response)
            })
            .catch((error) => console.log(error))
    }

    wordCloudButton.addEventListener('click', () => {
        fetchWordCloud()
    })

    keywordMapButton.addEventListener('click', () => {
        fetchKeywordMap()
    })

    fetchWordCloud()
})
