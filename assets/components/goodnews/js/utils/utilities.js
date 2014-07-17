/**
 * Check whether an object is Array or not
 * @type Boolean
 * @param {object} subject is the variable that is tested for Array identity check
 */
var isArray = (function () {
    // Use compiler's own isArray when available
    if (Array.isArray) {
        return Array.isArray;
    }
 
    // Retain references to variables for performance
    // optimization
    var objectToStringFn = Object.prototype.toString,
        arrayToStringResult = objectToStringFn.call([]);
 
    return function (subject) {
        return objectToStringFn.call(subject) === arrayToStringResult;
    };
}());
