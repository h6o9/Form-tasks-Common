function swap(arr, i, j) {
	let temp = arr[i];
	arr[i] = arr[j];
	arr[j] = temp;
}

function seperate(arr) {
	let i = 0;
	let j = arr.length-1;
	while(i <= j) {
		if(arr[i] === 1) {
			swap(arr, i,j);
			j--;
		} else [
			i++
		]
	}

	return arr;
}


let arr = [1,0,0,1,0,1];
let result = seperate(arr);
console.log(result);

/// 

let earr = [0, 2, 1, 2, 0, 1, 0];
let start = 0;
let end = earr.length - 1;

for (let i = 0; i <= earr.length; i++) {
    if (start < end) {
        // Yahan par koi swap ya logic array elements par apply nahi ho raha hai
        // sirf variables 'start' aur 'end' ki values badal rahi hain
        let temp = start;
        start = end; // start ko end ki value mili
        end = temp;   // end ko purani start ki value mili
    }
    // Agar aap yahan return karte hain to loop sirf ek baar chalega
    // return earr; // Agar aap is line ko yahan se hata den to array mil jayega
}

console.log(earr); // Output: [0, 2, 1, 2, 0, 1, 0] (Array change nahi hua)

